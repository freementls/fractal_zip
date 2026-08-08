<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Execution Operators - Manual</title>
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
 <link rel="index" href="language.operators.php" />
 <link rel="prev" href="language.operators.errorcontrol.php" />
 <link rel="next" href="language.operators.increment.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.execution" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.execution.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{HE9AXPCB}" />
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
 <li class="header up"><a href="langref.php">Language Reference</a></li>
 <li class="header up"><a href="language.operators.php">Operators</a></li>
 <li><a href="language.operators.precedence.php">Operator Precedence</a></li>
 <li><a href="language.operators.arithmetic.php">Arithmetic Operators</a></li>
 <li><a href="language.operators.assignment.php">Assignment Operators</a></li>
 <li><a href="language.operators.bitwise.php">Bitwise Operators</a></li>
 <li><a href="language.operators.comparison.php">Comparison Operators</a></li>
 <li><a href="language.operators.errorcontrol.php">Error Control Operators</a></li>
 <li class="active"><a href="language.operators.execution.php">Execution Operators</a></li>
 <li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li>
 <li><a href="language.operators.logical.php">Logical Operators</a></li>
 <li><a href="language.operators.string.php">String Operators</a></li>
 <li><a href="language.operators.array.php">Array Operators</a></li>
 <li><a href="language.operators.type.php">Type Operators</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.operators.increment.php">Incrementing/Decrementing Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.errorcontrol.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Error Control Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.execution.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.execution.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.execution.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.execution.php">French</option>
    <option value="de/language.operators.execution.php">German</option>
    <option value="ja/language.operators.execution.php">Japanese</option>
    <option value="pl/language.operators.execution.php">Polish</option>
    <option value="ro/language.operators.execution.php">Romanian</option>
    <option value="ru/language.operators.execution.php">Russian</option>
    <option value="fa/language.operators.execution.php">Persian</option>
    <option value="es/language.operators.execution.php">Spanish</option>
    <option value="tr/language.operators.execution.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.execution" class="sect1">
   <h2 class="title">Execution Operators</h2>
   <p class="para">
    PHP supports one execution operator: backticks (``). Note that
    these are not single-quotes! PHP will attempt to execute the
    contents of the backticks as a shell command; the output will be
    returned (i.e., it won&#039;t simply be dumped to output; it can be
    assigned to a variable).  Use of the backtick operator is identical
    to  <span class="function"><a href="function.shell-exec.php" class="function">shell_exec()</a></span>.
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$output&nbsp;</span><span style="color: #007700">=&nbsp;`</span><span style="color: #DD0000">ls&nbsp;-al</span><span style="color: #007700">`;<br />echo&nbsp;</span><span style="color: #DD0000">"&lt;pre&gt;</span><span style="color: #0000BB">$output</span><span style="color: #DD0000">&lt;/pre&gt;"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     The backtick operator is disabled when <a href="ini.sect.safe-mode.php#ini.safe-mode" class="link">safe mode</a> is enabled
     or  <span class="function"><a href="function.shell-exec.php" class="function">shell_exec()</a></span> is disabled.
    </p>
   </p></blockquote>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     Unlike some other languages, backticks cannot be used within
     double-quoted strings.
    </p>
   </p></blockquote>
   <p class="para">
    See also the manual section on <a href="ref.exec.php" class="link">Program
    Execution functions</a>,  <span class="function"><a href="function.popen.php" class="function">popen()</a></span>
     <span class="function"><a href="function.proc-open.php" class="function">proc_open()</a></span>, and
    <a href="features.commandline.php" class="link">Using PHP from the
    commandline</a>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.increment.php">Incrementing/Decrementing Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.errorcontrol.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Error Control Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.execution.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.execution&amp;redirect=@w{HE9AXPCB}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.execution&amp;redirect=@w{HE9AXPCB}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Execution Operators</strong>
 </div><div id="allnotes">
 <a name="101823"></a>
 <div class="note">
  <strong class='user'>joseph dot bruni at gmail dot com</strong>
  <a href="#101823" class="date">12-Jan-2011 03:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you have problems with the buffer size for the return result, use the popen() command instead. This will allow you to do file I/O on the result without any buffer limits.</span>
</code></div>
  </div>
 </div>
 <a name="62489"></a>
 <div class="note">
  <strong class='user'>robert</strong>
  <a href="#62489" class="date">01-Mar-2006 11:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just a general usage note.&nbsp; I had a very difficult time solving a problem with my script, when I accidentally put one of these backticks at the beginning of a line, like so:<br />
<br />
[lots of code]<br />
`&nbsp; &nbsp; $URL = "blah...";<br />
[more code]<br />
<br />
Since the backtick is right above the tab key, I probably just fat-fingered it while indenting the code.<br />
<br />
What made this so hard to find, was that PHP reported a parse error about 50 or so lines *below* the line containing the backtick.&nbsp; (There were no other backticks anywhere in my code.)&nbsp; And the error message was rather cryptic:<br />
<br />
Parse error: parse error, expecting `T_STRING' or `T_VARIABLE' or `T_NUM_STRING' in /blah.php on line 446<br />
<br />
Just something to file away in case you're pulling your hair out trying to find an error that "isn't there."</span>
</code></div>
  </div>
 </div>
 <a name="60212"></a>
 <div class="note">
  <strong class='user'>cs at kainaw dot com</strong>
  <a href="#60212" class="date">29-Dec-2005 02:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
After much trouble, I have concluded that the backtick operator (and shell_exec) have a limited buffer for the return.&nbsp; My problem was that I was grepping a file with over 500,000 lines, receiving a response with well over 100,000 lines.&nbsp; After a short pause, I was flooded with errors from grep about the pipe being closed.<br />
<br />
I have searched, but I cannot find the exact size of the buffer used by the backtick operator and shell_exec.&nbsp; So, to avoid this error, you must limit the output of your commands (such as using -m with grep).&nbsp; Through trial and error, you can get the command to run without error.</span>
</code></div>
  </div>
 </div>
 <a name="60001"></a>
 <div class="note">
  <strong class='user'>shakil dot tanvir at gmail dot com</strong>
  <a href="#60001" class="date">22-Dec-2005 05:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For passing parameter to a executable doesn't need an executable. Also it may create problem specifically for CGI Bin aplicatioin. Have a look at the following code:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$parFile</span><span class="keyword">=</span><span class="string">"param.txt"</span><span class="keyword">;<br />
</span><span class="default">$parImage</span><span class="keyword">=</span><span class="string">"mohona.gif"</span><span class="keyword">;<br />
<br />
</span><span class="default">$output</span><span class="keyword">=`</span><span class="default">C:\ms4w\Apache\cgi-bin\owtchart.exe $parFile $parImage</span><span class="keyword">`;<br />
echo </span><span class="string">"&lt;pre&gt;$output&lt;/pre&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
In the above code "owtchart.exe" takes two parameters. One is a text file(param.txt) and another is a name of a GIF file where output will be created. It works fine and doesn't need any BAT file!</span>
</code></div>
  </div>
 </div>
 <a name="58986"></a>
 <div class="note">
  <strong class='user'>vdboor at codingdomain dot com</strong>
  <a href="#58986" class="date">23-Nov-2005 12:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that most OS-es define two channels for file-output, the stdout and stderr (standard out and standard error). To read the data sent to stderr too, include 2&gt;&amp;1 in the backticks.</span>
</code></div>
  </div>
 </div>
 <a name="34273"></a>
 <div class="note">
  <strong class='user'>aaron dot bentley at utoronto dot ca</strong>
  <a href="#34273" class="date">20-Jul-2003 04:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
waylanator's example can be dangerous, since it doesn't prevent characters with special meaning from being emitted to the commandline.&nbsp; Programming errors or untrusted data could cause serious problems.&nbsp; At the bare minimum, remove all non-alphanumeric characters before passing a string to the shell.&nbsp; escapeshellarg() is also useful in *nix environments, but usually the best approach is to bypass the shell, using exec() etc.</span>
</code></div>
  </div>
 </div>
 <a name="32324"></a>
 <div class="note">
  <strong class='user'>thierry_bo at php dot net</strong>
  <a href="#32324" class="date">23-May-2003 10:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
About the french page and french keyboards : backtick is on the 7 key (7 è `), not the english pound key (£ $ ¤).</span>
</code></div>
  </div>
 </div>
 <a name="30775"></a>
 <div class="note">
  <strong class='user'>dexter at ragehosting dot net</strong>
  <a href="#30775" class="date">27-Mar-2003 11:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Or just %* (i think) to pass ALL variables specified</span>
</code></div>
  </div>
 </div>
 <a name="29481"></a>
 <div class="note">
  <strong class='user'>waylanator no at spam hotmail dot com</strong>
  <a href="#29481" class="date">14-Feb-2003 12:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Want to pass a parameter with your batch file to the executable?<br />
Just do this:<br />
<br />
mybat.bat:<br />
__________<br />
@echo off<br />
c:\progra~1\myprog~1\program.exe %1<br />
__________<br />
<br />
php:<br />
__________<br />
<span class="default">&lt;?php<br />
$par</span><span class="keyword">= </span><span class="string">"my_parameter"</span><span class="keyword">;<br />
</span><span class="default">$test</span><span class="keyword">=`</span><span class="default">c:\mybat.bat $par</span><span class="keyword">`;<br />
echo </span><span class="string">"&lt;pre&gt;$test&lt;/pre&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>__________<br />
<br />
Have more than 1 parameter?&nbsp; Just add %2 %3 %4 and so on in the batch file.<br />
<br />
Hope this helps someone.</span>
</code></div>
  </div>
 </div>
 <a name="29479"></a>
 <div class="note">
  <strong class='user'>waylanator no at spam hotmail dot com</strong>
  <a href="#29479" class="date">14-Feb-2003 09:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In Windows it appears you can only call an executable file that resides in the system path which is defined by Windows.&nbsp; As a workaround you can place a batch file in the system path that calls the program from it's dir. Just make sure to use short MS-DOS file and dir names.<br />
For example:<br />
If you were calling the file c:\program files\my program\program.exe do this:<br />
<br />
mybat.bat look like this:<br />
_________<br />
@echo off<br />
c:\progra~1\myprog~1\program.exe<br />
_________<br />
<br />
Save mybat.bat in c:\ or c:\windows or any other dir in the system path as defined by windows.<br />
<br />
Then in php call the batch file:<br />
_________<br />
<span class="default">&lt;?php<br />
$test </span><span class="keyword">= `</span><span class="default">c:\mybat.bat</span><span class="keyword">`;<br />
echo </span><span class="string">"&lt;pre&gt;$test&lt;/pre&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span>_________<br />
<br />
That should do it.<br />
Of course this will only work for a program you can run from the MS-DOS command prompt, but (as I understant it) that goes for any executable you call with PHP anyway.<br />
Tested in Win98 running Apache 1.3.27 and PHP 4.3.0</span>
</code></div>
  </div>
 </div>
 <a name="19848"></a>
 <div class="note">
  <strong class='user'>reed-NO at SPAM-zerohour dot net</strong>
  <a href="#19848" class="date">13-Mar-2002 12:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When a program is run using backticks, and the user cancels page loading (if your program is taking too long!), the shell running the program (the one in the backticks) may continue indefinitely on the server. I do not know if this is a bug, or just a danger of using this feature.&nbsp; (It may depend on the way the browser "cancels" the request -- it was a problem on both IE and OmniWeb for the Mac).&nbsp; Beware!</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.execution&amp;redirect=@w{HE9AXPCB}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.execution&amp;redirect=@w{HE9AXPCB}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.execution.php">show source</a> |
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