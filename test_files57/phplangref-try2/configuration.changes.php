<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: How to change configuration settings - Manual</title>
 <style type="text/css" media="all">
  @import url("@w{2XX58MCD}");
  @import url("@w{884KPP5P}");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("@w{M98RFPWS}");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 <link rel="shortcut icon" href="@w{NGWYKJ8F}" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="configuration.php" />
 <link rel="prev" href="configuration.changes.modes.php" />
 <link rel="next" href="langref.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/configuration.changes" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/configuration.changes.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/configuration.changes.php" />
 <meta http-equiv="Content-language" content="en" />
            <script type="text/javascript" src="@w{ME5H2G8Y}"></script>
            <script type="text/javascript" src="@w{BYSKBGP9}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var toggleImage = function(elem) {
        if ($(elem).hasClass("shown")) {
            $(elem).removeClass("shown").addClass("hidden");
            $("img", elem).attr("src", "/images/notes-add.gif");
        }
        else {
            $(elem).removeClass("hidden").addClass("shown");
            $("img", elem).attr("src", "/images/notes-reject.gif");
        }
    };

    $(".soft-deprecation-notice h1.title").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='minimize' /></a> ");
    });
    $(".refsect1 h3.title").each(function() {
        url = "@w{BD87E369}" + $(this).parent().parent().attr("id") + "%23" + $(this).parent().attr("id");
        $(this).parent().prepend("<div class='reportbug'><a href='" + url + "'>Report a bug</a></div>");
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $("#usernotes .head").each(function() {
        $(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' alt='reject note' /></a> ");
    });
    $(".soft-deprecation-notice h1.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $(".refsect1 h3.title .toggler").click(function() {
        $(this).parent().siblings().slideToggle("slow");
        toggleImage(this);
        return false;
    });
    $("#usernotes .head .toggler").click(function() {
        $(this).parent().next().slideToggle("slow");
        toggleImage(this);
        return false;
    });
});
</script>

</head>
<body>

<div id="headnav">
 <a href="/" rel="home"><img src="@w{BJ2SG82M}"
 alt="PHP" width="120" height="67" id="phplogo" /></a>
 <div id="headmenu">
  <a href="/downloads.php">downloads</a> |
  <a href="/docs.php">documentation</a> |
  <a href="/FAQ.php">faq</a> |
  <a href="/support.php">getting help</a> |
  <a href="/mailing-lists.php">mailing lists</a> |
  <a href="/license">licenses</a> |
  <a href="@w{WEGCK3BV}">wiki</a> |
  <a href="@w{JBVFFY7T}">reporting bugs</a> |
  <a href="/sites.php">php.net sites</a> |
  <a href="/conferences/">conferences</a> |
  <a href="/my.php">my php.net</a>
 </div>
</div>

<div id="headsearch">
 <form method="post" action="/search.php" id="topsearch">
  <p>
   <span title="Keyboard shortcut: Alt+S (Win), Ctrl+S (Apple)">
    <span class="shortkey">s</span>earch for
   </span>
   <input type="text" name="pattern" value="" size="30" accesskey="s" />
   <span>in the</span>
   <select name="show">
    <option value="all"      >all php.net sites</option>
    <option value="local"    >this mirror only</option>
    <option value="quickref" selected="selected">function list</option>
    <option value="manual"   >online documentation</option>
    <option value="bugdb"    >bug database</option>
    <option value="news_archive">Site News Archive</option>
    <option value="changelogs">All Changelogs</option>
    <option value="pear"     >just pear.php.net</option>
    <option value="pecl"     >just pecl.php.net</option>
    <option value="talks"    >just talks.php.net</option>
    <option value="maillist" >general mailing list</option>
    <option value="devlist"  >developer mailing list</option>
    <option value="phpdoc"   >documentation mailing list</option>
   </select>
   <input type="image"
          src="@w{XXWWP636}"
          class="submit" alt="search" />
   <input type="hidden" name="lang" value="en" />
  </p>
 </form>
</div>

<div id="layout_2">
 <div id="leftbar">
<!--UdmComment-->
<ul class="toc">
 <li class="header home"><a href="index.php">PHP Manual</a></li>
 <li class="header up"><a href="install.php">Installation and Configuration</a></li>
 <li class="header up"><a href="configuration.php">Runtime Configuration</a></li>
 <li><a href="configuration.file.php">The configuration file</a></li>
 <li><a href="configuration.file.per-user.php">.user.ini files</a></li>
 <li><a href="configuration.changes.modes.php">Where a configuration setting may be set</a></li>
 <li class="active"><a href="configuration.changes.php">How to change configuration settings</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="langref.php">Language Reference<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="configuration.changes.modes.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Where a configuration setting may be set</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/configuration.changes.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/configuration.changes.php">Brazilian Portuguese</option>
    <option value="zh/configuration.changes.php">Chinese (Simplified)</option>
    <option value="fr/configuration.changes.php">French</option>
    <option value="de/configuration.changes.php">German</option>
    <option value="ja/configuration.changes.php">Japanese</option>
    <option value="pl/configuration.changes.php">Polish</option>
    <option value="ro/configuration.changes.php">Romanian</option>
    <option value="ru/configuration.changes.php">Russian</option>
    <option value="fa/configuration.changes.php">Persian</option>
    <option value="es/configuration.changes.php">Spanish</option>
    <option value="tr/configuration.changes.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="configuration.changes" class="sect1">
  <h2 class="title">How to change configuration settings</h2>

  <div class="sect2" id="configuration.changes.apache">
   <h3 class="title">Running PHP as an Apache module</h3>
   <p class="simpara">
    When using PHP as an Apache module, you can also change the
    configuration settings using directives in Apache configuration
    files (e.g. <var class="filename">httpd.conf</var>) and <var class="filename">.htaccess</var> files. You will need
    &quot;AllowOverride Options&quot; or &quot;AllowOverride All&quot; privileges to do so.
   </p>

   <p class="para">
    There are several Apache directives that allow you
    to change the PHP configuration from within the Apache configuration
    files.  For a listing of which directives are
    <strong><code>PHP_INI_ALL</code></strong>, <strong><code>PHP_INI_PERDIR</code></strong>,
    or <strong><code>PHP_INI_SYSTEM</code></strong>, have a look at the
    <a href="ini.list.php" class="link">List of php.ini directives</a> appendix.
   </p>

   <p class="para">
    <dl>

     <dt>

      <span class="term">
       <code class="systemitem systemitem">php_value</code>
       <em><code class="parameter">name</code></em>
       <em><code class="parameter">value</code></em>
      </span>
      <dd>

       <p class="para">
        Sets the value of the specified directive.
        Can be used only with <strong><code>PHP_INI_ALL</code></strong> and <strong><code>PHP_INI_PERDIR</code></strong> type directives.
        To clear a previously set value use <em>none</em> as the value.
       </p>
       <blockquote class="note"><p><strong class="note">Note</strong>: 
        <span class="simpara">
         Don&#039;t use <code class="systemitem systemitem">php_value</code> to set boolean values.
         <code class="systemitem systemitem">php_flag</code> (see below) should be used instead.
        </span>
       </p></blockquote>
      </dd>

     </dt>

     <dt>

      <span class="term">
       <code class="systemitem systemitem">php_flag</code>
       <em><code class="parameter">name</code></em>
       <em><code class="parameter">on|off</code></em>
      </span>
      <dd>

       <p class="para">
        Used to set a boolean configuration directive.
        Can be used only with <strong><code>PHP_INI_ALL</code></strong> and
        <strong><code>PHP_INI_PERDIR</code></strong> type directives.
       </p>
      </dd>

     </dt>

     <dt>

      <span class="term">
       <code class="systemitem systemitem">php_admin_value</code>
       <em><code class="parameter">name</code></em>
       <em><code class="parameter">value</code></em>
      </span>
      <dd>

       <p class="para">
        Sets the value of the specified directive.
        This <em class="emphasis">can not be used</em> in <var class="filename">.htaccess</var> files.
        Any directive type set with <code class="systemitem systemitem">php_admin_value</code>
        can not be overridden by <var class="filename">.htaccess</var> or  <span class="function"><a href="function.ini-set.php" class="function">ini_set()</a></span>.
        To clear a previously set value use <em>none</em> as the value.
       </p>
      </dd>

     </dt>

     <dt>

      <span class="term">
       <code class="systemitem systemitem">php_admin_flag</code>
       <em><code class="parameter">name</code></em>
       <em><code class="parameter">on|off</code></em>
      </span>
      <dd>

       <p class="para">
        Used to set a boolean configuration directive.
        This <em class="emphasis">can not be used</em> in <var class="filename">.htaccess</var> files.
        Any directive type set with <code class="systemitem systemitem">php_admin_flag</code>
        can not be overridden by <var class="filename">.htaccess</var> or  <span class="function"><a href="function.ini-set.php" class="function">ini_set()</a></span>.
       </p>
      </dd>

     </dt>

    </dl>

   </p>
   <p class="para">
    <div class="example" id="example-63">
     <p><strong>Example #1 Apache configuration example</strong></p>
     <div class="example-contents">
<div class="inicode"><pre class="inicode">&lt;IfModule mod_php5.c&gt;
  php_value include_path &quot;.:/usr/local/lib/php&quot;
  php_admin_flag engine on
&lt;/IfModule&gt;
&lt;IfModule mod_php4.c&gt;
  php_value include_path &quot;.:/usr/local/lib/php&quot;
  php_admin_flag engine on
&lt;/IfModule&gt;</pre>
</div>
     </div>

    </div>
   </p>
   <div class="caution"><strong class="caution">Caution</strong>
    <p class="para">
     PHP constants do not exist outside of PHP. For example, in
     <var class="filename">httpd.conf</var> you can not use PHP constants
     such as <strong><code>E_ALL</code></strong> or <strong><code>E_NOTICE</code></strong>
     to set the <a href="errorfunc.configuration.php#ini.error-reporting" class="link">error_reporting</a>
     directive as they will have no meaning and will evaluate to
     <em class="emphasis">0</em>. Use the associated bitmask values instead.
     These constants can be used in <var class="filename">php.ini</var>
    </p>
   </div>
  </div>

  <div class="sect2" id="configuration.changes.windows">
   <h3 class="title">Changing PHP configuration via the Windows registry</h3>
    <p class="simpara">
     When running PHP on Windows, the configuration values can be
     modified on a per-directory basis using the Windows registry. The
     configuration values are stored in the registry key
     <em>HKLM\SOFTWARE\PHP\Per Directory Values</em>,
     in the sub-keys corresponding to the path names. For example, configuration
     values for the directory <em>c:\inetpub\wwwroot</em> would
     be stored in the key <em>HKLM\SOFTWARE\PHP\Per Directory
     Values\c\inetpub\wwwroot</em>. The settings for the
     directory would be active for any script running from this
     directory or any subdirectory of it. The values under the key
     should have the name of the PHP configuration directive and the
     string value. PHP constants in the values are not parsed.
     However, only configuration values changeable in
     <strong><code>PHP_INI_USER</code></strong> can be set
     this way, <strong><code>PHP_INI_PERDIR</code></strong> values can not.
    </p>
  </div>

  <div class="sect2" id="configuration.changes.other">
   <h3 class="title">Other interfaces to PHP</h3>
   <p class="para">
    Regardless of how you run PHP, you can change certain values at runtime
    of your scripts through  <span class="function"><a href="function.ini-set.php" class="function">ini_set()</a></span>. See the documentation
    on the  <span class="function"><a href="function.ini-set.php" class="function">ini_set()</a></span> page for more information.
   </p>
   <p class="para">
    If you are interested in a complete list of configuration settings
    on your system with their current values, you can execute the
     <span class="function"><a href="function.phpinfo.php" class="function">phpinfo()</a></span> function, and review the resulting
    page. You can also access the values of individual configuration
    directives at runtime using  <span class="function"><a href="function.ini-get.php" class="function">ini_get()</a></span> or
     <span class="function"><a href="function.get-cfg-var.php" class="function">get_cfg_var()</a></span>.
   </p>
  </div>
 </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="langref.php">Language Reference<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="configuration.changes.modes.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Where a configuration setting may be set</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/configuration.changes.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=configuration.changes&amp;redirect=http://www.php.net/manual/en/configuration.changes.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=configuration.changes&amp;redirect=http://www.php.net/manual/en/configuration.changes.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>How to change configuration settings</strong>
 </div><div id="allnotes">
 <a name="107058"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#107058" class="date">03-Jan-2012 08:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For Mac users (Leopard/Tiger/Lion) it's worth knowing exactly where your httpd.conf and php.ini ares picked up, but, out of the box, these are defined only in the compiler settings as defaults.<br />
<br />
The httpd binary was compiled with default settings of <br />
SERVER_CONFIG_FILE="/private/etc/apache2/httpd.conf"<br />
meaning starting apache using apachectl picks up this httpd.conf without it being specified on the command line via the org.apache.httpd.plist file is /System/Library/LaunchDaemons.<br />
<br />
Again, the default httpd.conf does not contain a PHPIniDir, and locates php.ini via libphp5.so in /etc - you would need to create one here (using one of the php.ini.default templates if you want).<br />
<br />
It's worth changing the PHPIniDir setting in httpd.conf setting to eg.:<br />
<br />
Configuration File&nbsp; &nbsp;&nbsp; /usr/local/php5/lib/php.ini<br />
<br />
so you know exactly which php.ini is being used at all times.<br />
<br />
phpinfo() will then advise you that it's using this file.</span>
</code></div>
  </div>
 </div>
 <a name="105770"></a>
 <div class="note">
  <strong class='user'>user at NOSPAM dot example dot com</strong>
  <a href="#105770" class="date">13-Sep-2011 11:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP Constants will work with php_value; for example:<br />
&nbsp;<br />
php_value error_reporting 30711 #bitmask is the same as<br />
php_value error_reporting "E_ALL &amp; ~E_STRICT &amp; ~E_NOTICE" #string of constants</span>
</code></div>
  </div>
 </div>
 <a name="104657"></a>
 <div class="note">
  <strong class='user'>rohitkumar at cftechno dot com</strong>
  <a href="#104657" class="date">29-Jun-2011 12:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Open php.ini from c:/php in your text editor<br />
<br />
If you installed Apache in the default location, the path should<br />
be "C:\Program Files\Apache Software Foundation\Apache2.2\htdocs". If you installed<br />
it elsewhere, find the htdocs folder and type its path:<br />
<br />
doc_root = "C:\Program Files\Apache Software Foundation\Apache2.2\htdocs"<br />
<br />
Just a little further down in the file, look for the line that begins with remove the semicolon from the start of the extension_<br />
dir, and set it so that it points to the ext subfolder of your PHP folder:<br />
extension_dir = "C:\PHP\ext"<br />
<br />
These are optional extensions to PHP, disabled by default. We<br />
want to enable the MySQL extension so that PHP can communicate with MySQL.<br />
To do this, remove the semicolon from the start of the php_mysqli.dll line:<br />
extension=php_mysqli.dll<br />
<br />
note - php_mysqli, not php_mysql<br />
<br />
Keep scrolling even further down in the file, and look for a line that starts with<br />
;session.save_path. Once again, remove the semicolon to enable this line,<br />
and set it to your Windows Temp folder:<br />
session.save_path = "C:\Windows\Temp"<br />
<br />
Browse to the conf subfolder in your Apache<br />
installation folder (by default, C:\Program Files\Apache Software<br />
Foundation\Apache2.2\conf), and select the httpd.conf file located there. In order<br />
to make this file visible for selection, you’ll need to select All Files (*.*) from the<br />
file type drop-down menu at the bottom of the Open window.<br />
Look for the existing line in this file that begins with DirectoryIndex, shown<br />
here:<br />
&lt;IfModule dir_module&gt;<br />
DirectoryIndex index.html<br />
&lt;/IfModule&gt;<br />
<br />
This line tells Apache which filenames to use when it looks for the default page<br />
for a given directory. Add index.php to the end of this line:<br />
&lt;IfModule dir_module&gt;<br />
DirectoryIndex index.html index.php<br />
&lt;/IfModule&gt;<br />
<br />
All of the remaining options in this long and intimidating configuration file<br />
should have been set up correctly by the Apache install program. All you need<br />
to do is add the following lines to the very end of the file:<br />
<br />
LoadModule php5_module "C:/PHP/php5apache2_2.dll"<br />
AddType application/x-httpd-php .php<br />
PHPIniDir "C:/PHP"<br />
<br />
Make sure the LoadModule and PHPIniDir lines point to your PHP installation<br />
directory, and note the use of forward slashes (/) instead of backslashes (\) in<br />
the paths.<br />
<br />
Save your changes and Restart Apache using the Apache Service Monitor system tray icon. If all is<br />
well, Apache will start up again without complaint.</span>
</code></div>
  </div>
 </div>
 <a name="102163"></a>
 <div class="note">
  <strong class='user'>fay at prospekt-omsk dot ru</strong>
  <a href="#102163" class="date">31-Jan-2011 12:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
x64 based Windows registry path is<br />
<br />
HKEY_LOCAL_MACHINE\SOFTWARE\Wow6432Node\PHP\Per Directory Values</span>
</code></div>
  </div>
 </div>
 <a name="91520"></a>
 <div class="note">
  <strong class='user'>self at pabloviquez dot com</strong>
  <a href="#91520" class="date">14-Jun-2009 06:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that, changing the PHP configuration via the windows registry will set the new values using php_admin_value. This makes that you cannot override them on runtime.<br />
<br />
So for example, if you set the include_path on the windows registry and then you call the set_include_path function in your application, it will return false and won't change the include_path.</span>
</code></div>
  </div>
 </div>
 <a name="90679"></a>
 <div class="note">
  <strong class='user'>cawoodm at gmail dot com</strong>
  <a href="#90679" class="date">04-May-2009 07:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
With IIS it can become confusing that changes to php.ini are ineffective. It seems it is necessary to restart the application pool for the changes to be seen. It would be great if this was not necessary - I am sure I have worked with systems where php.ini changes were immediately effectvie.</span>
</code></div>
  </div>
 </div>
 <a name="89459"></a>
 <div class="note">
  <strong class='user'>ravi at syntric dot net</strong>
  <a href="#89459" class="date">08-Mar-2009 10:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a quick note regarding per-directory php settings. A number of flags can be set custom to a particular app's requirement based on one of two methods describe her <a href="http://www.cognitivecombine.com/?p=207" rel="nofollow" target="_blank">http://www.cognitivecombine.com/?p=207</a><br />
<br />
I particularly stumbled upon this to turn off register_globals. Drupal, for e.g., required this to be off but I knew of numerous php apps that had code which relied on these globals. The .htaccess method described in the above URL worked for my case.</span>
</code></div>
  </div>
 </div>
 <a name="88816"></a>
 <div class="note">
  <strong class='user'>hyponiq at gmail dot com</strong>
  <a href="#88816" class="date">09-Feb-2009 09:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Running PHP under Apache poses a major problem when it comes to per-directory configuration settings for PHP.&nbsp; In Apache virtual hosting, only a master PHP configuration file (i.e. php.ini) is parsed at run-time per PHP script.&nbsp; Under IIS 6.0 or greater, you can include per-directory PHP configuration files to override or overwrite the master configuration settings.&nbsp; The issue here, however, is having Apache virtual hosts override/overwrite master settings; not what IIS can do.<br />
<br />
So, there are two possible solutions.&nbsp; The first solution is described in this section and uses the Apache configuration settings php_value, php_flag, php_admin_value, and php_admin_flag.&nbsp; In that, each virtual host which you'd like to have certain configuration settings changed must have these directives set (and that is for each PHP configuration setting).&nbsp; This, to me, is the more viable solution, although it is time-consuming and mentally taxing.<br />
<br />
The other possible solution is to set the PHPRC environment variable.&nbsp; To my knowledge, all implementations of Apache HTTPD allow for the SetEnv directive to set the PHPRC variable per-virtual-host.&nbsp; What that does is tell PHP to look in the specified location for that virtual host's configuration settings file (i.e. "C:/path/to/custom/php.ini").&nbsp; The only downside to this tactic is that EVERY virtual host's custom php.ini file must contain all set parameters.&nbsp; In other words, every single PHP configuration directive you have set in the master php.ini file must ALSO be set in per-virtual-host configuration settings.&nbsp; Doesn't that suck?&nbsp; It seems rather redundant to me (and completely defeats the purpose) that you have to include all configuration settings OVER AND OVER AGAIN.<br />
<br />
The great thing about per-directory configuration settings (when they're implemented correctly) is that PHP already has the master settings pre-loaded and the per-directory settings (which may only ammount to one directive in difference) can be loaded per request and, thus, there is less over-head.<br />
<br />
There really is more to this topic than that, which is why I blogged a rather lengthy and detailed article here: <a href="http://hyponiq.blogspot.com/2009/02/apache-php-multiple-phpini.html&nbsp;" rel="nofollow" target="_blank">http://hyponiq.blogspot.com/2009/02/apache-php-multiple-phpini.html&nbsp;</a> This information is meant to help users and administrators.&nbsp; I highly suggest it be read if anyone has any questions on Apache and PHP configurations.&nbsp; I have included some examples that illustrate the two possible solutions, as well.&nbsp; I did my best to research everything before I wrote the article.<br />
<br />
I hope this helps!<br />
<br />
<br />
==== 10-FEB-09: ====<br />
I must add a little more information:<br />
<br />
I've done some thorough testing on my PC as to the PHPRC environment variable set by the Apache directive SetEnv.&nbsp; It seems to me that this variable is completely disregarded using that directive.&nbsp; I tried everything and can only come to the conclusion that either A) I did something very wrong, or B) that it simply doesn't work as expected.<br />
<br />
The former solution, however, does work magically!&nbsp; So, to expand on my previous post, the only real and viable solution to this problem is to use the php_value, php_flag, php_admin_value and php_admin_flag directives in your virtual hosts configurations.<br />
<br />
Once again, it can be very boring!&nbsp; But it does work.</span>
</code></div>
  </div>
 </div>
 <a name="85935"></a>
 <div class="note">
  <strong class='user'>ludek dot stepan at gmail dot com</strong>
  <a href="#85935" class="date">25-Sep-2008 04:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hello,<br />
<br />
I've found this directive useful for setting per-file php.ini options. For example, when I want to have my .css styles processed as php scripts, I put this code into .htaccess to setup correct mimetype.<br />
<br />
AddHandler php5-script .css<br />
&lt;FilesMatch "\.css$"&gt;<br />
&nbsp;&nbsp;&nbsp; php_value default_mimetype "text/css"<br />
&lt;/FilesMatch&gt;<br />
<br />
Yours Ludek</span>
</code></div>
  </div>
 </div>
 <a name="80831"></a>
 <div class="note">
  <strong class='user'>contrees.du.reve at gmail dot com</strong>
  <a href="#80831" class="date">02-Feb-2008 05:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Being able to put php directives in httpd.conf and have them work on a per-directory or per-vitual host basis is just great. Now there's another aspect which might be worth being aware of:<br />
<br />
A php.ini directive put into your apache conf file applies to php when it runs as an apache module (i.e. in a web page), but NOT when it runs as CLI (command-line interface).<br />
<br />
Such feature that might be unwanted by an unhappy few, but I guess most will find it useful. As far as I'm concerned, I'm really happy that I can use open_basedir in my httpd.conf file, and it restricts the access of web users and sub-admins&nbsp; of my domain, but it does NOT restrict my own command-line php scripts...</span>
</code></div>
  </div>
 </div>
 <a name="76362"></a>
 <div class="note">
  <strong class='user'>webmaster at htaccesselite dot com</strong>
  <a href="#76362" class="date">11-Jul-2007 08:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To change the configuration for php running as cgi those handy module commands won't work.. The work-around is being able to tell php to start with a custom php.ini file.. configured the way you want.<br />
<br />
&nbsp;With multiple custom php.ini files<br />
-------------------------------------------<br />
/site/ini/1/php.ini<br />
/site/ini/2/php.ini<br />
/site/ini/3/php.ini<br />
--<br />
<br />
The trick is creating a wrapper script to set the location of the php.ini file that php will use. Then it exec's the php cgi.<br />
<br />
&nbsp;shell script /cgi-bin/phpini.cgi<br />
-------------------------------------------<br />
#!/bin/sh<br />
export PHPRC=/site/ini/1<br />
exec /cgi-bin/php5.cgi<br />
--<br />
<br />
Now all you have to do is setup Apache to run php files through the wrapper script instead of just executing the php cgi.<br />
<br />
&nbsp;In your .htaccess or httpd.conf file<br />
-------------------------------------------<br />
AddHandler php-cgi .php<br />
Action php-cgi /cgi-bin/phpini.cgi<br />
--<br />
<br />
So to change the configuration of php you just need to change the PHPRC variable to point to a different directory containing your customized php.ini.. You could also create multiple shell wrapper scripts and create multiple Handler's+Actions in .htaccess.. <br />
<br />
&nbsp;in your .htaccess<br />
-------------------------------------------<br />
AddHandler php-cgi1 .php1<br />
Action php-cgi1 /cgi-bin/phpini-1.cgi<br />
<br />
AddHandler php-cgi2 .php2<br />
Action php-cgi2 /cgi-bin/phpini-2.cgi<br />
<br />
AddHandler php-cgi3 .php3<br />
Action php-cgi3 /cgi-bin/phpini-3.cgi<br />
--<br />
<br />
The only caveat here is that it seems like you would have to rename the file extensions, but there are ways around that too -&gt;<br />
<a href="http://www.askapache.com/php/custom-phpini-tips-and-tricks.html" rel="nofollow" target="_blank">http://www.askapache.com/php/custom-phpini-tips-and-tricks.html</a></span>
</code></div>
  </div>
 </div>
 <a name="76285"></a>
 <div class="note">
  <strong class='user'>Woody/mC</strong>
  <a href="#76285" class="date">09-Jul-2007 06:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@ pgl: As the documentation says:<br />
<br />
"To clear a previously set value use none as the value."<br />
<br />
Works fine for me.</span>
</code></div>
  </div>
 </div>
 <a name="76035"></a>
 <div class="note">
  <strong class='user'>pgl at yoyo dot org</strong>
  <a href="#76035" class="date">27-Jun-2007 03:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It is not possible to unset a config option using php_value. This caused me problems with auto_prepend_file settings where I wanted to have a global file auto included, with an exception for only one site. The solution used to be to use auto_prepend_file /dev/null, but this now causes errors, so I just create and include blank.inc now instead.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=configuration.changes&amp;redirect=http://www.php.net/manual/en/configuration.changes.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=configuration.changes&amp;redirect=http://www.php.net/manual/en/configuration.changes.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/configuration.changes.php">show source</a> |
 <a href="/credits.php">credits</a> |
 <a href="/stats/">stats</a> |
 <a href="/sitemap.php">sitemap</a> |
 <a href="/contact.php">contact</a> |
 <a href="/contact.php#ads">advertising</a> |
 <a href="/mirrors.php">mirror sites</a>
</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="/copyright.php">Copyright &copy; 2001-2012 The PHP Group</a><br />
  All rights reserved.
 </div>

 <div id="thismirror">
  <a href="/mirror.php">This mirror</a> generously provided by:
  <a href="@w{TDAY9QJ9}">Yahoo! Inc.</a><br />
  Last updated: Tue Jul 31 20:41:05 2012 UTC
 </div>
</div>
<!--[if IE 6]>
<script type="text/javascript">
    /*Load jQuery if not already loaded*/ if(typeof jQuery == 'undefined'){ document.write("<script type=\"text/javascript\"   src=\"@w{8JFFCNVW}"></"+"script>"); var __noconflict = true; }
    var IE6UPDATE_OPTIONS = {
        icons_path: "/ie6update/images/"
    }
</script>
<script type="text/javascript" src="/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>