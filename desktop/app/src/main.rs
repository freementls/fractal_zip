//! fractal_zip desktop v0.1 — native OS window (WebView) + local PHP UI server.
//!
//! Layout expected next to this executable (release zip):
//!   fz-desktop          ← this binary
//!   payload/            ← library + examples + hub (from assemble_payload.sh)
//!   runtime/php[.exe]   ← optional bundled PHP; else system `php` on PATH

use std::env;
use std::fs;
use std::io::{Read, Write};
use std::net::TcpListener;
use std::path::{Path, PathBuf};
use std::process::{Child, Command, Stdio};
use std::sync::atomic::{AtomicBool, Ordering};
use std::sync::Arc;
use std::thread;
use std::time::{Duration, Instant};

use tao::event::{Event, WindowEvent};
use tao::event_loop::{ControlFlow, EventLoop};
use tao::window::WindowBuilder;
#[cfg(target_os = "linux")]
use tao::platform::unix::WindowExtUnix;
use wry::WebViewBuilder;
#[cfg(target_os = "linux")]
use wry::WebViewBuilderExtUnix;

struct PhpServer {
	child: Child,
	url: String,
}

impl Drop for PhpServer {
	fn drop(&mut self) {
		let _ = self.child.kill();
		let _ = self.child.wait();
	}
}

fn app_root() -> PathBuf {
	if let Ok(exe) = env::current_exe() {
		if let Some(dir) = exe.parent() {
			return dir.to_path_buf();
		}
	}
	env::current_dir().unwrap_or_else(|_| PathBuf::from("."))
}

fn find_php(root: &Path) -> PathBuf {
	let candidates = [
		root.join("runtime").join("php"),
		root.join("runtime").join("bin").join("php"),
		root.join("runtime").join("php.exe"),
		root.join("runtime").join("php").join("php.exe"),
	];
	for c in candidates {
		if c.is_file() {
			return c;
		}
	}
	PathBuf::from("php")
}

fn free_port(preferred: u16) -> u16 {
	for offset in 0..40u16 {
		let port = preferred.saturating_add(offset);
		if TcpListener::bind(("127.0.0.1", port)).is_ok() {
			return port;
		}
	}
	preferred
}

fn wait_for_http(url: &str, timeout: Duration) -> bool {
	let start = Instant::now();
	let Some(rest) = url.strip_prefix("http://") else {
		return false;
	};
	let Some((hostport, _)) = rest.split_once('/') else {
		return false;
	};
	let Some((host, port_s)) = hostport.split_once(':') else {
		return false;
	};
	let Ok(port) = port_s.parse::<u16>() else {
		return false;
	};

	while start.elapsed() < timeout {
		if let Ok(mut stream) = std::net::TcpStream::connect((host, port)) {
			let req = b"GET / HTTP/1.0\r\nHost: 127.0.0.1\r\nConnection: close\r\n\r\n";
			let _ = stream.write_all(req);
			let _ = stream.set_read_timeout(Some(Duration::from_millis(300)));
			let mut buf = [0u8; 24];
			if let Ok(n) = stream.read(&mut buf) {
				if n > 0 {
					return true;
				}
			}
		}
		thread::sleep(Duration::from_millis(80));
	}
	false
}

fn jobs_dir(root: &Path) -> PathBuf {
	if let Ok(v) = env::var("FRACTAL_ZIP_WEB_JOBS") {
		if !v.trim().is_empty() {
			return PathBuf::from(v);
		}
	}
	#[cfg(target_os = "macos")]
	{
		let _ = root;
		let home = env::var("HOME").unwrap_or_else(|_| ".".into());
		return PathBuf::from(home)
			.join("Library")
			.join("Application Support")
			.join("fractal_zip")
			.join("web_jobs");
	}
	#[cfg(target_os = "windows")]
	{
		let _ = root;
		let base = env::var("LOCALAPPDATA").unwrap_or_else(|_| ".".into());
		return PathBuf::from(base).join("fractal_zip").join("web_jobs");
	}
	#[cfg(not(any(target_os = "macos", target_os = "windows")))]
	{
		let _ = root;
		let base = env::var("XDG_DATA_HOME").unwrap_or_else(|_| {
			let home = env::var("HOME").unwrap_or_else(|_| ".".into());
			format!("{home}/.local/share")
		});
		PathBuf::from(base).join("fractal_zip").join("web_jobs")
	}
}

fn start_php(root: &Path) -> Result<PhpServer, String> {
	let payload = root.join("payload");
	let router = payload.join("router.php");
	if !router.is_file() {
		return Err(format!(
			"Missing payload/next to the executable.\nExpected: {}\nRun desktop/scripts/package.sh or use a release zip.",
			router.display()
		));
	}

	let php = find_php(root);
	let preferred: u16 = env::var("FRACTAL_ZIP_DESKTOP_PORT")
		.ok()
		.and_then(|s| s.parse().ok())
		.unwrap_or(17865);
	let port = free_port(preferred);
	let host = env::var("FRACTAL_ZIP_DESKTOP_HOST").unwrap_or_else(|_| "127.0.0.1".into());
	let url = format!("http://{host}:{port}/");

	let jobs = jobs_dir(root);
	fs::create_dir_all(&jobs).map_err(|e| format!("Cannot create jobs dir {}: {e}", jobs.display()))?;

	let mut cmd = Command::new(&php);
	cmd.arg("-d")
		.arg("upload_max_filesize=2G")
		.arg("-d")
		.arg("post_max_size=2G")
		.arg("-d")
		.arg("max_file_uploads=2000")
		.arg("-d")
		.arg("max_execution_time=0")
		.arg("-d")
		.arg("memory_limit=1024M")
		.arg("-S")
		.arg(format!("{host}:{port}"))
		.arg("-t")
		.arg(&payload)
		.arg(&router)
		.env("FZC_WEB_MAX_UPLOAD_BYTES", env::var("FZC_WEB_MAX_UPLOAD_BYTES").unwrap_or_else(|_| "0".into()))
		.env(
			"FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES",
			env::var("FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES").unwrap_or_else(|_| "0".into()),
		)
		.env(
			"FRACTAL_ZIP_PHP",
			env::var("FRACTAL_ZIP_PHP").unwrap_or_else(|_| {
				payload
					.join("fractal_zip.php")
					.to_string_lossy()
					.into_owned()
			}),
		)
		.env("FRACTAL_ZIP_SUPPRESS_HTML", "1")
		.env("FRACTAL_ZIP_WEB_JOBS", &jobs)
		.stdin(Stdio::null())
		.stdout(Stdio::null())
		.stderr(Stdio::null());

	let child = cmd.spawn().map_err(|e| {
		format!(
			"Failed to start PHP ({}) : {e}\nInstall PHP 8.1+ or place a binary at runtime/php",
			php.display()
		)
	})?;

	let server = PhpServer { child, url: url.clone() };
	if !wait_for_http(&url, Duration::from_secs(8)) {
		return Err(format!(
			"PHP server did not become ready at {url}. Is PHP working? ({})",
			php.display()
		));
	}
	Ok(server)
}

fn read_version(root: &Path) -> String {
	let p = root.join("VERSION");
	fs::read_to_string(p)
		.ok()
		.map(|s| s.trim().to_string())
		.filter(|s| !s.is_empty())
		.unwrap_or_else(|| "0.1.0".into())
}

fn main() {
	let root = app_root();
	let version = read_version(&root);

	let server = match start_php(&root) {
		Ok(s) => s,
		Err(msg) => {
			eprintln!("fractal_zip desktop: {msg}");
			std::process::exit(1);
		}
	};
	let url = server.url.clone();

	let running = Arc::new(AtomicBool::new(true));
	let running_watch = running.clone();
	// Keep server alive for the window lifetime; drop kills PHP.
	let mut _server = Some(server);

	let event_loop = EventLoop::new();
	let window = WindowBuilder::new()
		.with_title(format!("fractal_zip {version}"))
		.with_inner_size(tao::dpi::LogicalSize::new(960.0, 720.0))
		.build(&event_loop)
		.expect("create window");

	let builder = WebViewBuilder::new().with_url(&url);

	#[cfg(target_os = "linux")]
	let _webview = {
		let vbox = window.default_vbox().expect("tao default vbox");
		builder.build_gtk(vbox).expect("create webview")
	};

	#[cfg(not(target_os = "linux"))]
	let _webview = builder.build(&window).expect("create webview");

	event_loop.run(move |event, _, control_flow| {
		*control_flow = ControlFlow::Wait;
		if let Event::WindowEvent {
			event: WindowEvent::CloseRequested,
			..
		} = event
		{
			running_watch.store(false, Ordering::SeqCst);
			_server.take(); // kill PHP
			*control_flow = ControlFlow::Exit;
		}
	});
}
