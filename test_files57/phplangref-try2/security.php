<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Security - Manual</title>
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
 <link rel="index" href="index.php" />
 <link rel="prev" href="wrappers.expect.php" />
 <link rel="next" href="security.intro.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/security" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="alternate" href="/manual/en/feeds/security.atom" type="application/atom+xml" />
 <link rel="canonical" href="http://php.net/manual/en/security.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/security.php" />
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
 <li><a href="copyright.php">Copyright</a></li>
 <li><a href="manual.php">PHP Manual</a></li>
 <li><a href="getting-started.php">Getting Started</a></li>
 <li><a href="install.php">Installation and Configuration</a></li>
 <li><a href="langref.php">Language Reference</a></li>
 <li class="active"><a href="security.php">Security</a></li>
 <li><a href="features.php">Features</a></li>
 <li><a href="funcref.php">Function Reference</a></li>
 <li><a href="internals2.php">PHP at the Core: A Hacker's Guide to the Zend Engine</a></li>
 <li><a href="faq.php">FAQ</a></li>
 <li><a href="appendices.php">Appendices</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="security.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.expect.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />expect://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/security.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/security.php">Brazilian Portuguese</option>
    <option value="zh/security.php">Chinese (Simplified)</option>
    <option value="fr/security.php">French</option>
    <option value="de/security.php">German</option>
    <option value="ja/security.php">Japanese</option>
    <option value="pl/security.php">Polish</option>
    <option value="ro/security.php">Romanian</option>
    <option value="ru/security.php">Russian</option>
    <option value="fa/security.php">Persian</option>
    <option value="es/security.php">Spanish</option>
    <option value="tr/security.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="security" class="book">
  <h1 class="title">Security</h1>
  


 



  


  



  


  



  


  



  


  



  


  



  


  



  


  



  






  

  



  


  




  


  




 <ul class="chunklist chunklist_book"><li><a href="security.intro.php">Introduction</a></li><li><a href="security.general.php">General considerations</a></li><li><a href="security.cgi-bin.php">Installed as CGI binary</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="security.cgi-bin.attacks.php">Possible attacks</a></li><li><a href="security.cgi-bin.default.php">Case 1: only public files served</a></li><li><a href="security.cgi-bin.force-redirect.php">Case 2: using cgi.force_redirect</a></li><li><a href="security.cgi-bin.doc-root.php">Case 3: setting doc_root or user_dir</a></li><li><a href="security.cgi-bin.shell.php">Case 4: PHP parser outside of web tree</a></li></ul></li><li><a href="security.apache.php">Installed as an Apache module</a></li><li><a href="security.filesystem.php">Filesystem Security</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="security.filesystem.nullbytes.php">Null bytes related issues</a></li></ul></li><li><a href="security.database.php">Database Security</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="security.database.design.php">Designing Databases</a></li><li><a href="security.database.connection.php">Connecting to Database</a></li><li><a href="security.database.storage.php">Encrypted Storage Model</a></li><li><a href="security.database.sql-injection.php">SQL Injection</a></li></ul></li><li><a href="security.errors.php">Error Reporting</a></li><li><a href="security.globals.php">Using Register Globals</a></li><li><a href="security.variables.php">User Submitted Data</a></li><li><a href="security.magicquotes.php">Magic Quotes</a><ul class="chunklist chunklist_book chunklist_children"><li><a href="security.magicquotes.what.php">What are Magic Quotes</a></li><li><a href="security.magicquotes.why.php">Why did we use Magic Quotes</a></li><li><a href="security.magicquotes.whynot.php">Why not to use Magic Quotes</a></li><li><a href="security.magicquotes.disabling.php">Disabling Magic Quotes</a></li></ul></li><li><a href="security.hiding.php">Hiding PHP</a></li><li><a href="security.current.php">Keeping Current</a></li></ul></div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="security.intro.php">Introduction<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="wrappers.expect.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />expect://</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/security.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=security&amp;redirect=http://www.php.net/manual/en/security.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=security&amp;redirect=http://www.php.net/manual/en/security.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Security</strong>
 </div><div id="allnotes">
 <a name="109188"></a>
 <div class="note">
  <strong class='user'>Lionel</strong>
  <a href="#109188" class="date">26-Jun-2012 06:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Features for manipulation of input data:<br />
<br />
- Additional or lack of form variables<br />
It will send only pass variables from a form provided by the attacker set and not all the form variables like the real thing.<br />
BSP:<br />
&lt;form ...&gt;<br />
&lt;input name="name"&gt;<br />
&lt;input name="password"&gt;<br />
&lt;input name="optional"&gt;<br />
<br />
$ _POST ['Name'] = "root";<br />
$ _POST ['Password'] = "12345";<br />
$ _POST ['Optional'] would be missing<br />
<br />
- Session ID does not match the standard<br />
Session ID is guessed or tried themselves together, making it too short or contains incorrect characters<br />
BSP:<br />
<a href="http://www.example.com/?sid=ABCDabcd1234" rel="nofollow" target="_blank">http://www.example.com/?sid=ABCDabcd1234</a><br />
instead of<br />
<a href="http://www.example.com/?sid=ABCD%" rel="nofollow" target="_blank">http://www.example.com/?sid=ABCD%</a> 2Cabcd-1234<br />
<br />
- Hidden, select, checkbox variables do not correspond to the model<br />
The transmitted values ​​are different from the predefined values ​​of the above fields<br />
BSP:<br />
&lt;select name="auswahl"&gt;<br />
&lt;option value="1"&gt; One &lt;/ option&gt;<br />
&lt;option value="2"&gt; Two &lt;/ option&gt;<br />
&lt;option value="3"&gt; Three &lt;/ option&gt;<br />
&lt;/ select&gt;<br />
<br />
$ _POST ['Selection'] = 'a';<br />
No integer value<br />
<br />
- Source of variables (get, post, cookie) do not match<br />
The attacker tries to parameter passing some variables to set or influence<br />
BSP:<br />
<a href="http://example.com/?login=true" rel="nofollow" target="_blank">http://example.com/?login=true</a><br />
<br />
$ _GET ['Login'] = true<br />
<br />
instead of<br />
<br />
$ _COOKIE ['Login'] = true</span>
</code></div>
  </div>
 </div>
 <a name="76363"></a>
 <div class="note">
  <strong class='user'>moehbass at gmail dot com</strong>
  <a href="#76363" class="date">11-Jul-2007 10:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
First, q much simpler solution to preventing people from viewing code inside of an includable file would be to give include file an extension that ends with php (e.g. myFile.inc.php).<br />
<br />
Secondly, and more importantly, why on earth would you want to put program-level code in an include file? By that I mean something life this:<br />
<br />
myFile.inc.php<br />
--------------------------------<br />
...<br />
if ($var = 'whatever')<br />
&nbsp;&nbsp;&nbsp; // connect to the database<br />
else<br />
&nbsp;&nbsp;&nbsp; // do something else.<br />
--------------------------------<br />
<br />
An include file should not contain logic! Rather, it is an encapsulated unit of code that should not do anything on its own unless asked to. To implement this ideology, consider including function definitions only in your include files, then once you include them in the script, call such functions from within your program (i.e. the script that included the inc file). If you don't know the names of the functions ab initio, use call_user_func() or call_user_func_array() and pass it the name of the function that's dependent on context.<br />
<br />
If you MUST put program-level logic in your include files, consider simply putting it in the program!<br />
<br />
Why should you consider this? How about variable name clashes for a starter! You can think of more, I am shure!<br />
<br />
Hope that helped</span>
</code></div>
  </div>
 </div>
 <a name="72806"></a>
 <div class="note">
  <strong class='user'>dangan at blackjaguargaming dot net</strong>
  <a href="#72806" class="date">01-Feb-2007 06:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I'd recommend a 404 over a 403 considering a 403 proves there is something worth hacking into.<br />
<br />
index.php:<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'isdoc'</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">);<br />
include(</span><span class="string">'includes/include.sqlfunctions.php'</span><span class="keyword">);<br />
</span><span class="comment">// Rest of code for index.php<br />
</span><span class="default">?&gt;<br />
</span><br />
include.sqlfunctions.php (or other include file):<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(</span><span class="default">isdoc </span><span class="keyword">!== </span><span class="default">1</span><span class="keyword">) </span><span class="comment">// Not identical to 1<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">header</span><span class="keyword">(</span><span class="string">'HTTP/1.1 404 Not Found'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"&lt;!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"&gt;\n&lt;html&gt;&lt;head&gt;\n&lt;title&gt;404 Not Found&lt;/title&gt;\n&lt;/head&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"&lt;body&gt;\n&lt;h1&gt;Not Found&lt;/h1&gt;\n&lt;p&gt;The requested URL "</span><span class="keyword">.</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'REQUEST_URI'</span><span class="keyword">].</span><span class="string">" was not found on this server.&lt;/p&gt;\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"&lt;hr&gt;\n"</span><span class="keyword">.</span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">'SERVER_SIGNATURE'</span><span class="keyword">].</span><span class="string">"\n&lt;/body&gt;&lt;/html&gt;\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Echo output similar to Apache's default 404 (if thats what you're using)<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">exit;<br />
}<br />
</span><span class="comment">// Rest of code for this include<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="70740"></a>
 <div class="note">
  <strong class='user'>k</strong>
  <a href="#70740" class="date">25-Oct-2006 04:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
How about not putting the php code in the web-root at all...?<br />
<br />
You can create a public directory with the css, html, etc and index.php there. Then use the include_path setting to point to the actual php code, eg...<br />
<br />
webstuff<br />
&nbsp; phpcode<br />
&nbsp; public<br />
&nbsp;&nbsp;&nbsp; images<br />
&nbsp;&nbsp;&nbsp; css<br />
&nbsp;&nbsp;&nbsp; index.php<br />
<br />
then set the include path to "../phpcode" and, as php is executed from the directory of the script, all should be well.<br />
<br />
I'd also call the main index "main.page", or something else, instead of "index.php" and change the web server default index page. That way you cant get hit by things trawlling the web for index pages.</span>
</code></div>
  </div>
 </div>
 <a name="69775"></a>
 <div class="note">
  <strong class='user'>steffen at morkland dot com</strong>
  <a href="#69775" class="date">20-Sep-2006 06:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In Reply to djjokla and others<br />
<br />
Consider placing all incude files as mentioned before in a seperate folder containing a .htaccess containing a Order Deny,Allow<br />
<br />
the create a index file, which is intended to handle ALL request made to you php application, then call it with index.php?view=index<br />
<br />
the index file could look a bit like this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">switch(</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'view'</span><span class="keyword">]){<br />
&nbsp;&nbsp;&nbsp; case </span><span class="string">'index'</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; include(</span><span class="string">'libs/index.php'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp;&nbsp; default:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; include(</span><span class="string">'libs/404.php'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; break;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
this could be an array or something even more creative. it actually does'nt matter how you do it... running all pages through one central script has one big advantage.... CONTROL.<br />
at any givin time, you can easily implement access control to functions without forgetting crucial files.</span>
</code></div>
  </div>
 </div>
 <a name="64840"></a>
 <div class="note">
  <strong class='user'>djjokla AT gmail dot com</strong>
  <a href="#64840" class="date">21-Apr-2006 08:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If a single file has to be included than I use the following<br />
<br />
index.php ( where the file is gonna be included )<br />
___________<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; define</span><span class="keyword">(</span><span class="string">'thefooter'</span><span class="keyword">, </span><span class="default">TRUE</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; include(</span><span class="string">'folder/footer.inc.php'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
and the footer file (for example) looks this way then<br />
<br />
footer.inc.php ( the file to be inluded )<br />
___________<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; defined</span><span class="keyword">(</span><span class="string">'thefooter'</span><span class="keyword">) or die(</span><span class="string">'Not with me my friend'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">'Copyright to me in the year 2000'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
So when someone tries to access the footer.php file directly he/she/it will get the "Not with me my friend" messages written on the screen. An alternative option is to redirect the person who wants to access the file directly to a different location, so instead of the above code you would have to write the following in the footer.inc.php file.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; defined</span><span class="keyword">(</span><span class="string">'thefooter'</span><span class="keyword">) or </span><span class="default">header</span><span class="keyword">(</span><span class="string">'Location: <a href="http://www.location.com" rel="nofollow" target="_blank">http://www.location.com</a>'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; echo(</span><span class="string">'Copyright to me in the year 2000'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
In normal case a redirection to an external site would be annoying to the visitor, but since this visitor is more interested in hacking the site than in reading the content, I think it's only fair to create such an redirection. We dont' realy want someome like this on our sites.<br />
<br />
For the file protection I use .htaccess in which I say to protect the file itself and every .inc file<br />
<br />
&lt;Files ~ "^.*\.([Hh][Tt]|[Ii][Nn][Cc])"&gt;<br />
Order allow,deny<br />
Deny from all<br />
Satisfy All<br />
&lt;/Files&gt;<br />
<br />
The .htaccess file should result an Error 403 if someone tries to access the files directly. If for some reason this shouldn't work, then the "Not with me my friend" text apears or a redirection (depending what is used)<br />
<br />
In my eyes this looks o.k. and safe.</span>
</code></div>
  </div>
 </div>
 <a name="59814"></a>
 <div class="note">
  <strong class='user'>webmaster �tt christophdum ddott com</strong>
  <a href="#59814" class="date">16-Dec-2005 04:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
that's cool, but i use this code right here:<br />
<br />
&lt;?if(!defined('IN_SCRIPT')){header('HTTP/1.0 404 not found');exit;}?&gt;<br />
<br />
adding a 404 header will not give the user any clue that the include-file even exists !!!<br />
<br />
i also protect the whole include-directory with a .htaccess file that says: "Deny from all"<br />
<br />
so i guess that's pretty secure</span>
</code></div>
  </div>
 </div>
 <a name="57851"></a>
 <div class="note">
  <strong class='user'>Thomas &quot;Balu&quot; Walter</strong>
  <a href="#57851" class="date">16-Oct-2005 01:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since many users can not modify apache configurations or use htaccess files, the best way to avoid unwanted access to include files would be a line at the beginning of the include-file:<br />
<br />
<span class="default">&lt;?php </span><span class="keyword">if (!</span><span class="default">defined</span><span class="keyword">(</span><span class="string">'APPLICATION'</span><span class="keyword">)) exit; </span><span class="default">?&gt;<br />
</span><br />
And in all files that are allowed to be called externally:<br />
<br />
<span class="default">&lt;?php define</span><span class="keyword">(</span><span class="string">'APPLICATION'</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">); </span><span class="default">?&gt;<br />
</span><br />
&nbsp;&nbsp; &nbsp; Balu</span>
</code></div>
  </div>
 </div>
 <a name="45336"></a>
 <div class="note">
  <strong class='user'>nick dot hristov at gmail dot com</strong>
  <a href="#45336" class="date">02-Sep-2004 08:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A correction to previous post by Dave Mink.<br />
<br />
&lt;Files ~ "\.inc$"&gt;<br />
&nbsp;&nbsp; Order allow,deny<br />
&nbsp;&nbsp; Deny from all<br />
&nbsp;&nbsp; Satisfy All<br />
&lt;/Files&gt;<br />
<br />
Will not stop something like<br />
<a href="http://www.yourserver.com/includefile.inc?pointlessvar=blahblah" rel="nofollow" target="_blank">http://www.yourserver.com/includefile.inc?pointlessvar=blahblah</a><br />
<br />
Here is something more sophisticated for this task:<br />
<br />
&lt;Location ~ "/[^ ](?=\.inc(\?[^ ]*)?)/"&gt;<br />
&nbsp;&nbsp;&nbsp; Options None<br />
&nbsp;&nbsp;&nbsp; Order Allow, Deny<br />
&nbsp;&nbsp;&nbsp; Deny from All<br />
&nbsp;&nbsp;&nbsp; AllowOverride None<br />
&nbsp;&nbsp;&nbsp; Satisfy All<br />
&lt;/Location&gt;<br />
<br />
Also, consider placing in your httpd.conf<br />
<br />
&lt;Location ~ "/[^ ](?=\.phps(\?[^ ]*)?)/"&gt;<br />
&nbsp;&nbsp;&nbsp; Options None<br />
&nbsp;&nbsp;&nbsp; Order Allow, Deny<br />
&nbsp;&nbsp;&nbsp; Deny from All<br />
&nbsp;&nbsp;&nbsp; AllowOverride None<br />
&nbsp;&nbsp;&nbsp; Satisfy All<br />
&lt;/Location&gt;</span>
</code></div>
  </div>
 </div>
 <a name="33627"></a>
 <div class="note">
  <strong class='user'>ocrow at simplexity dot net</strong>
  <a href="#33627" class="date">02-Jul-2003 05:16</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If your PHP pages include() or require() files that live within the web server document root, for example library files in the same directory as the PHP pages, you must account for the possibility that attackers may call those library files directly.&nbsp; <br />
<br />
Any program level code in the library files (ie code not part of function definitions) will be directly executable by the caller outside of the scope of the intended calling sequence.&nbsp; An attacker may be able to leverage this ability to cause unintended effects.<br />
<br />
The most robust way to guard against this possibility is to prevent your webserver from calling the library scripts directly, either by moving them out of the document root, or by putting them in a folder configured to refuse web server access. With Apache for example, create a .htaccess file in the library script folder with these directives:<br />
<br />
Order Allow,Deny<br />
Deny from any</span>
</code></div>
  </div>
 </div>
 <a name="33483"></a>
 <div class="note">
  <strong class='user'>annonymous at domain dot com</strong>
  <a href="#33483" class="date">27-Jun-2003 06:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
best bet is to build php as cgi, run under suexec, with chroot jailed users. Not the best, but fairly unobtrusive, provides several levels of checkpoints, and has only the detriment of being, well, kinda slow. 8)</span>
</code></div>
  </div>
 </div>
 <a name="31678"></a>
 <div class="note">
  <strong class='user'>ManifoldNick at columbus dot rr dot com</strong>
  <a href="#31678" class="date">29-Apr-2003 10:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Remember that security risks often don't involve months of prep work or backdoors or whatever else you saw on Swordfish ;) In fact one of the bigges newbie mistakes is not removing "&lt;" from user input (especially when using message boards) so in theory a user could secerely mess up a page or even have your server run php scripts which would allow them to wreak havoc on your site.</span>
</code></div>
  </div>
 </div>
 <a name="29812"></a>
 <div class="note">
  <a href="#29812" class="date">25-Feb-2003 04:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For real security you should consider providing chrooted jail's for your users.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=security&amp;redirect=http://www.php.net/manual/en/security.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=security&amp;redirect=http://www.php.net/manual/en/security.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/security.php">show source</a> |
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