<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: include_once - Manual</title>
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
 <link rel="index" href="language.control-structures.php" />
 <link rel="prev" href="function.require-once.php" />
 <link rel="next" href="control-structures.goto.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/include-once" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/function.include-once.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/function.include-once.php" />
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
 <li class="header up"><a href="language.control-structures.php">Control Structures</a></li>
 <li><a href="control-structures.intro.php">Introduction</a></li>
 <li><a href="control-structures.if.php">if</a></li>
 <li><a href="control-structures.else.php">else</a></li>
 <li><a href="control-structures.elseif.php">elseif/else if</a></li>
 <li><a href="control-structures.alternative-syntax.php">Alternative syntax for control structures</a></li>
 <li><a href="control-structures.while.php">while</a></li>
 <li><a href="control-structures.do.while.php">do-while</a></li>
 <li><a href="control-structures.for.php">for</a></li>
 <li><a href="control-structures.foreach.php">foreach</a></li>
 <li><a href="control-structures.break.php">break</a></li>
 <li><a href="control-structures.continue.php">continue</a></li>
 <li><a href="control-structures.switch.php">switch</a></li>
 <li><a href="control-structures.declare.php">declare</a></li>
 <li><a href="function.return.php">return</a></li>
 <li><a href="function.require.php">require</a></li>
 <li><a href="function.include.php">include</a></li>
 <li><a href="function.require-once.php">require_<span class="w"> </span>once</a></li>
 <li class="active"><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="control-structures.goto.php">goto<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.require-once.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />require_once</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.include-once.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/function.include-once.php">Brazilian Portuguese</option>
    <option value="zh/function.include-once.php">Chinese (Simplified)</option>
    <option value="fr/function.include-once.php">French</option>
    <option value="de/function.include-once.php">German</option>
    <option value="ja/function.include-once.php">Japanese</option>
    <option value="pl/function.include-once.php">Polish</option>
    <option value="ro/function.include-once.php">Romanian</option>
    <option value="ru/function.include-once.php">Russian</option>
    <option value="fa/function.include-once.php">Persian</option>
    <option value="es/function.include-once.php">Spanish</option>
    <option value="tr/function.include-once.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="function.include-once" class="sect1">
 <h2 class="title">include_once</h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="para">
  The <em>include_once</em> statement includes and evaluates
  the specified file during the execution of the script.
  This is a behavior similar to the  <span class="function"><a href="function.include.php" class="function">include</a></span> statement,
  with the only difference being that if the code from a file has already
  been included, it will not be included again.  As the name suggests,
  it will be included just once.
 </p>
 <p class="para">
  <em>include_once</em> may be used in cases where
  the same file might be included and evaluated more than once during a
  particular execution of a script, so in this case it may help avoid
  problems such as function redefinitions, variable value reassignments, etc.
 </p>
 <p class="para">
  See the  <span class="function"><a href="function.include.php" class="function">include</a></span> documentation for information about
  how this function works.
 </p>
 <p class="para">
  <blockquote class="note"><p><strong class="note">Note</strong>: 
  <p class="para">
   With PHP 4, <em>_once</em> functionality differs with case-insensitive
   operating systems (like Windows) so for example:
   <div class="example" id="example-141">
    <p><strong>Example #1 <em>include_once</em> with a case insensitive OS in PHP 4</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">include_once&nbsp;</span><span style="color: #DD0000">"a.php"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;this&nbsp;will&nbsp;include&nbsp;a.php<br /></span><span style="color: #007700">include_once&nbsp;</span><span style="color: #DD0000">"A.php"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;this&nbsp;will&nbsp;include&nbsp;a.php&nbsp;again!&nbsp;(PHP&nbsp;4&nbsp;only)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    This behaviour changed in PHP 5, so for example with Windows the path is normalized first so that
    <var class="filename">C:\PROGRA~1\A.php</var> is realized the same as
    <var class="filename">C:\Program Files\a.php</var> and the file is included just once.
   </p>
  </p></blockquote>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.goto.php">goto<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.require-once.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />require_once</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.include-once.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=function.include-once&amp;redirect=http://www.php.net/manual/en/function.include-once.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.include-once&amp;redirect=http://www.php.net/manual/en/function.include-once.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>include_once</strong>
 </div><div id="allnotes">
 <a name="100045"></a>
 <div class="note">
  <strong class='user'>Frankbrennanfilms at yahoo dot com</strong>
  <a href="#100045" class="date">21-Sep-2010 01:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Need some help please. I keep getting this error:<br />
<br />
can not include<br />
Fatal error: Cannot redeclare get_postdata() <br />
<br />
Here's the solution I was told to put in, but it's&nbsp; not working<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(!@</span><span class="default">file_exists</span><span class="keyword">(</span><span class="string">'deprecated.php'</span><span class="keyword">) ) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'can not include'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp; include(</span><span class="string">'deprecated.php'</span><span class="keyword">);<br />
}<br />
<br />
&nbsp;* @</span><span class="default">package WordPress<br />
&nbsp;</span><span class="keyword">* @</span><span class="default">subpackage Deprecated<br />
&nbsp;</span><span class="keyword">*/</span>
</span>
</code></div>
  </div>
 </div>
 <a name="89963"></a>
 <div class="note">
  <strong class='user'>nospam at freeproxylist dot org</strong>
  <a href="#89963" class="date">30-Mar-2009 08:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I would like to share one very useful tip with include_* statements. For example we have two classes first.class.php and second.class.php both located in the same directory (./classes) and first one uses the second one. So we have:<br />
<br />
first.class.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include_once </span><span class="string">'./second.class.php'</span><span class="keyword">;<br />
...<br />
</span><span class="default">?&gt;<br />
</span><br />
also we have file which uses first.class.php:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include_once </span><span class="string">'./classes/first.class.php'</span><span class="keyword">;<br />
...<br />
</span><span class="default">?&gt;<br />
</span><br />
if you will try to execute your script you will get error. Reason: the current directory is different and the relative path in first.class.php (./second.class.php) will be incorrect.<br />
<br />
Here is two possible solution I have found:<br />
&nbsp;<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include_once </span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">).</span><span class="string">'/second.class.php'</span><span class="keyword">;<br />
...<br />
</span><span class="default">?&gt;<br />
</span><br />
or<br />
<br />
<span class="default">&lt;?php<br />
chdir</span><span class="keyword">(</span><span class="default">dirname</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">));<br />
include_once </span><span class="string">'./second.class.php'</span><span class="keyword">;<br />
...<br />
</span><span class="default">?&gt;<br />
</span><br />
Hope that tip will be useful for some other software developer<br />
<br />
Admin of <a href="http://FreeProxyList.org" rel="nofollow" target="_blank">http://FreeProxyList.org</a></span>
</code></div>
  </div>
 </div>
 <a name="88050"></a>
 <div class="note">
  <strong class='user'>neil at holcomb dot com</strong>
  <a href="#88050" class="date">07-Jan-2009 01:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using include_once() in the __autoload() function is redundant.&nbsp; __autoload() is only called when php can't find your class definition.&nbsp; If your file containg your class was already included, the class defenition would already be loaded and __autoload() would not be called.&nbsp; So save a little overhead and only use include() within __autoload()<br />
<br />
Neil Holcomb</span>
</code></div>
  </div>
 </div>
 <a name="84108"></a>
 <div class="note">
  <strong class='user'>roach dot scott+spam at googlemail dot com</strong>
  <a href="#84108" class="date">27-Jun-2008 05:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you include a file that does not exist with include_once, the return result will be false. <br />
<br />
If you try to include that same file again with include_once the return value will be true.<br />
<br />
Example:<br />
<span class="default">&lt;?php<br />
var_dump</span><span class="keyword">(include_once </span><span class="string">'fakefile.ext'</span><span class="keyword">); </span><span class="comment">// bool(false)<br />
</span><span class="default">var_dump</span><span class="keyword">(include_once </span><span class="string">'fakefile.ext'</span><span class="keyword">); </span><span class="comment">// bool(true)<br />
</span><span class="default">?&gt;<br />
</span><br />
This is because according to php the file was already included once (even though it does not exist).</span>
</code></div>
  </div>
 </div>
 <a name="83280"></a>
 <div class="note">
  <strong class='user'>emanuele at rogledi dot com</strong>
  <a href="#83280" class="date">18-May-2008 05:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For include_once a file in every paths of application we can do simply this<br />
<br />
include_once($_SERVER["DOCUMENT_ROOT"] . "mypath/my2ndpath/myfile.php");</span>
</code></div>
  </div>
 </div>
 <a name="76985"></a>
 <div class="note">
  <strong class='user'>php at metagg dot com</strong>
  <a href="#76985" class="date">08-Aug-2007 03:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are like me and make heavy use of the __autoload magic function, always set include paths so you can just instantiate your class, and have multiple locations and name schemes for your custom libraries then you might be frustrated by simple parse errors being supressed when using @include_once('lib.php').<br />
<br />
The solution I came up with was:<br />
<br />
define('IN_PRODUCTION_ENV',FALSE);<br />
<br />
function __autoload($class){<br />
<br />
&nbsp; $paths = array();<br />
&nbsp; $paths[] = "{$class}_lib.php";<br />
&nbsp; $paths[] = "{$class}_inc.php";<br />
&nbsp; $paths[] = "{$class}.php";<br />
<br />
&nbsp; if(IN_PRODUCTION_ENV){<br />
&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach($paths as &amp;$path){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; if((@include_once $path) !== false){ return; }//if<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }//foreach<br />
&nbsp; <br />
&nbsp; }else{<br />
&nbsp; <br />
&nbsp;&nbsp;&nbsp; // we are not in a production environment so we want to see all errors...<br />
&nbsp;&nbsp;&nbsp; $include_paths = explode(PATH_SEPARATOR,get_include_path());<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach($include_paths as $include_path){<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; // go through each of the different class names...<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach($paths as $path){<br />
&nbsp;&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // attach each class name to the include path...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $include_file = $include_path.$path;<br />
&nbsp;&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(file_exists($include_file)){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if((include_once $include_file) !== false){ return; }//if<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }//if<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; }//foreach<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }//foreach<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp; }//if/else<br />
&nbsp; <br />
&nbsp; trigger_error("{$class} was not found",E_USER_ERROR);<br />
&nbsp; <br />
}//method<br />
<br />
Now, just make sure you define IN_PRODUCTION_ENV to true or false to get either the slower (with all parse errors shown) or the faster (just suppress everything) autoloading. Hope this helps someone else since it was annoying just having blank screens show up when I had a simple parse error. Thanks to flobee at gmail dot com for providing me with the epiphany on why pages were showing up blank...-Metagg</span>
</code></div>
  </div>
 </div>
 <a name="68776"></a>
 <div class="note">
  <strong class='user'>webmaster AT domaene - kempten DOT de</strong>
  <a href="#68776" class="date">10-Aug-2006 05:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since I like to reuse a lot of code it came handy to me to begin some sort of library that I stored in a subdir<br />
e.g. "lib"<br />
<br />
The only thing that bothered me for some time was that although everything worked all IDEs reported during editing<br />
these useless warnings "file not found" when library files included other library files, since my path were given all relative to the corresponding document-root.<br />
<br />
Here is a short workaround that makes that gone:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Change to your path<br />
<br />
</span><span class="keyword">if(</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">__FILE__</span><span class="keyword">,</span><span class="string">'/lib/'</span><span class="keyword">) != </span><span class="default">FALSE</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">chdir</span><span class="keyword">(</span><span class="string">".."</span><span class="keyword">);<br />
}<br />
include_once (</span><span class="string">'./lib/other_lib.inc'</span><span class="keyword">);<br />
</span><span class="comment">// ... or any other include[_once] / require[_once]<br />
</span><span class="default">?&gt;<br />
</span><br />
just adjust the path and it will be fine - also for your IDE.<br />
<br />
greetings</span>
</code></div>
  </div>
 </div>
 <a name="56269"></a>
 <div class="note">
  <a href="#56269" class="date">29-Aug-2005 01:52</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Dealing with function redefinitions<br />
<br />
include_once and require_once are very useful if you have a library of common functions.&nbsp; If you try to override with - that is define - an identically named local function however, PHP will halt noting that it cannot redeclare functions.&nbsp; You can allow for this by bracketing (within the include file):<br />
function myUsefulFunc($arg1, $arg2) {<br />
&nbsp;&nbsp; &nbsp; ... }<br />
<br />
with<br />
<br />
if (!function_exists('myUsefulFunc')) {<br />
function myUsefulFunc($arg1, $arg2) {<br />
&nbsp;&nbsp; &nbsp; ... }}<br />
<br />
Top level functions (ie. those not defined within other functions or dependent on code running) in the local file are always parsed first, so <a href="http://php.net/function_exists" rel="nofollow" target="_blank">http://php.net/function_exists</a> within the included/required file is safe - it doesn't matter where the include statements are in the local code.<br />
<br />
Csaba Gabor from Vienna</span>
</code></div>
  </div>
 </div>
 <a name="53239"></a>
 <div class="note">
  <strong class='user'>flobee at gmail dot com</strong>
  <a href="#53239" class="date">26-May-2005 07:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
i already had a discussion with several people about "not shown errors"<br />
error reporting and all others in php.ini set to: "show errors" to find problems: <br />
the answer i finally found:<br />
if you have an "@include..." instead of "include..." or "require..('somthing') in any place in your code <br />
all following errors are not shown too!!!<br />
<br />
so, this is actually a bad idea when developing because paser errors will be droped too:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(!@include_once(</span><span class="string">'./somthing'</span><span class="keyword">) ) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'can not include'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
solution:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(!@</span><span class="default">file_exists</span><span class="keyword">(</span><span class="string">'./somthing'</span><span class="keyword">) ) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'can not include'</span><span class="keyword">;<br />
} else {<br />
&nbsp;&nbsp; include(</span><span class="string">'./something'</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="51043"></a>
 <div class="note">
  <strong class='user'>Pure-PHP</strong>
  <a href="#51043" class="date">17-Mar-2005 02:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Inlude_once can slower your app, if you include to many files.<br />
<br />
You cann use this wrapper class, it is faster than include_once <br />
<br />
<a href="http://www.pure-php.de/node/19" rel="nofollow" target="_blank">http://www.pure-php.de/node/19</a><br />
<br />
include_once("includeWrapper.class.php")<br />
<br />
includeWrapper::includeOnce("Class1.class.php");<br />
includeWrapper::requireOnce("Class1.class.php");<br />
includeWrapper::includeOnce("Class2.class.php")</span>
</code></div>
  </div>
 </div>
 <a name="46970"></a>
 <div class="note">
  <strong class='user'>bioster at peri dot csclub dot uwaterloo dot ca</strong>
  <a href="#46970" class="date">28-Oct-2004 03:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Something to be wary of:&nbsp; When you use include_once and the data that you include falls out of scope, if you use include_once again later it will not include despite the fact that what you included is no longer available.<br />
<br />
So you should be wary of using include_once inside functions.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=function.include-once&amp;redirect=http://www.php.net/manual/en/function.include-once.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.include-once&amp;redirect=http://www.php.net/manual/en/function.include-once.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/function.include-once.php">show source</a> |
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