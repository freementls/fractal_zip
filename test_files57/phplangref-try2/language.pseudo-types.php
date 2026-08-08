<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Pseudo-types and variables used in this documentation - Manual</title>
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
 <link rel="index" href="language.types.php" />
 <link rel="prev" href="language.types.callable.php" />
 <link rel="next" href="language.types.type-juggling.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/pseudo-types" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.pseudo-types.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.pseudo-types.php" />
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
 <li class="header up"><a href="language.types.php">Types</a></li>
 <li><a href="language.types.intro.php">Introduction</a></li>
 <li><a href="language.types.boolean.php">Booleans</a></li>
 <li><a href="language.types.integer.php">Integers</a></li>
 <li><a href="language.types.float.php">Floating point numbers</a></li>
 <li><a href="language.types.string.php">Strings</a></li>
 <li><a href="language.types.array.php">Arrays</a></li>
 <li><a href="language.types.object.php">Objects</a></li>
 <li><a href="language.types.resource.php">Resources</a></li>
 <li><a href="language.types.null.php">NULL</a></li>
 <li><a href="language.types.callable.php">Callbacks</a></li>
 <li class="active"><a href="language.pseudo-types.php">Pseudo-types and variables used in this documentation</a></li>
 <li><a href="language.types.type-juggling.php">Type Juggling</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.types.type-juggling.php">Type Juggling<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.callable.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Callbacks</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.pseudo-types.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.pseudo-types.php">Brazilian Portuguese</option>
    <option value="zh/language.pseudo-types.php">Chinese (Simplified)</option>
    <option value="fr/language.pseudo-types.php">French</option>
    <option value="de/language.pseudo-types.php">German</option>
    <option value="ja/language.pseudo-types.php">Japanese</option>
    <option value="pl/language.pseudo-types.php">Polish</option>
    <option value="ro/language.pseudo-types.php">Romanian</option>
    <option value="ru/language.pseudo-types.php">Russian</option>
    <option value="fa/language.pseudo-types.php">Persian</option>
    <option value="es/language.pseudo-types.php">Spanish</option>
    <option value="tr/language.pseudo-types.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.pseudo-types" class="sect1">
 <h2 class="title">Pseudo-types and variables used in this documentation</h2> 

 <div class="sect2" id="language.types.mixed">
  <h3 class="title">mixed</h3>

  <p class="para">
   <em>mixed</em> indicates that a parameter may accept multiple (but
   not necessarily all) types.
  </p>

  <p class="para">
    <span class="function"><a href="function.gettype.php" class="function">gettype()</a></span> for example will accept all PHP types, while
    <span class="function"><a href="function.str-replace.php" class="function">str_replace()</a></span> will accept <span class="type"><a href="language.types.string.php" class="type string">string</a></span>s and
   <span class="type"><a href="language.types.array.php" class="type array">array</a></span>s.
  </p>

 </div>

 <div class="sect2" id="language.types.number">
  <h3 class="title">number</h3>

  <p class="para">
   <em>number</em> indicates that a parameter can be either
   <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span> or <span class="type"><a href="language.types.float.php" class="type float">float</a></span>.
  </p>

 </div>

 <div class="sect2" id="language.types.callback">
  <h3 class="title">callback</h3>

  <p class="para">
   <span class="type"><a href="language.pseudo-types.php#language.types.callback" class="type callback">callback</a></span> pseudo-types was used in this documentation before
   <span class="type"><a href="language.types.callable.php" class="type callable">callable</a></span> type hint was introduced by PHP 5.4. It means exactly
   the same.
  </p>
  
 </div>
 
 <div class="sect2" id="language.types.void">
  <h3 class="title">void</h3>

  <p class="para">
   <em>void</em> as a return type means that the return value is
   useless. <em>void</em> in a parameter list means that the function
   doesn&#039;t accept any parameters.
  </p>

 </div>

 <div class="sect2" id="language.types.dotdotdot">
  <h3 class="title">...</h3>

  <p class="para">
   <em><code class="parameter">$...</code></em> in function prototypes means
   <em>and so on</em>. This variable name is used when a function can
   take an endless number of arguments.
  </p>

 </div>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.types.type-juggling.php">Type Juggling<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.types.callable.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Callbacks</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.pseudo-types.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.pseudo-types&amp;redirect=http://www.php.net/manual/en/language.pseudo-types.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.pseudo-types&amp;redirect=http://www.php.net/manual/en/language.pseudo-types.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Pseudo-types and variables used in this documentation</strong>
 </div><div id="allnotes">
 <a name="101268"></a>
 <div class="note">
  <strong class='user'>liam at helios-sites dot com</strong>
  <a href="#101268" class="date">06-Dec-2010 04:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that (e.g.) usort calls on static methods of classes in a namespace need to be laid out as follows:<br />
<br />
usort($arr, array('\Namespace\ClassName', 'functionName'));</span>
</code></div>
  </div>
 </div>
 <a name="93234"></a>
 <div class="note">
  <strong class='user'>michael dot martinek at gmail dot com</strong>
  <a href="#93234" class="date">29-Aug-2009 09:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The documentation is a little confusing, and with the recent OO changes it adds a little more to the confusion.<br />
<br />
I was curious whether you could pass an object through the user func, modify it in that callback and have the actual object updated or whether some cloning was going on behind the scenes.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">Test<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; var </span><span class="default">$sValue </span><span class="keyword">= </span><span class="string">'abc'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">testing</span><span class="keyword">(</span><span class="default">$objTest</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$objTest</span><span class="keyword">-&gt;</span><span class="default">sValue </span><span class="keyword">= </span><span class="string">'123'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">();<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(array(</span><span class="default">$obj</span><span class="keyword">, </span><span class="string">'testing'</span><span class="keyword">), </span><span class="default">$obj</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This works as expected: The object is not cloned, and $sValue is properly set to '123'. With the OO changes in PHP 5, you don't need to do "function testing(&amp;$objTest)" as it is already passed by reference.</span>
</code></div>
  </div>
 </div>
 <a name="91482"></a>
 <div class="note">
  <strong class='user'>phpguy at lifetoward dot com</strong>
  <a href="#91482" class="date">11-Jun-2009 05:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I noticed two important thing about putting callbacks into an arg list when calling a function:<br />
<br />
1. The function to which the callback refers must be defined earlier in the source stream. So for example:<br />
<br />
function main() {...; usort($array, 'sortfunction'); ... }<br />
function sortfunction($a, $b){ return 0; }<br />
<br />
Will NOT work, but this will:<br />
<br />
function sortfunction($a, $b){ return 0; }<br />
function main() {...; usort($array, 'sortfunction'); ... }<br />
<br />
2. It's not really just a string. For example, this doesn't work:<br />
<br />
usort($array, ($reverse?'reversesorter':'forwardsorter'));<br />
<br />
I found these two discoveries quite counterintuitive.</span>
</code></div>
  </div>
 </div>
 <a name="90397"></a>
 <div class="note">
  <strong class='user'>sahid dot ferdjaoui at gmail dot com</strong>
  <a href="#90397" class="date">20-Apr-2009 03:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An example with PHP 5.3 and lambda functions<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; array_map </span><span class="keyword">(function (</span><span class="default">$value</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">MyFormElement </span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">);<br />
&nbsp; }, </span><span class="default">$_POST</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75329"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#75329" class="date">23-May-2007 10:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The mixed pseudotype is explained as meaning "multiple but not necessarily all" types, and the example of str_replace(mixed, mixed, mixed) is given where "mixed" means "string or array".<br />
Keep in mind that this refers to the types of the function's arguments _after_ any type juggling.</span>
</code></div>
  </div>
 </div>
 <a name="73104"></a>
 <div class="note">
  <strong class='user'>levi at alliancesoftware dot com dot au</strong>
  <a href="#73104" class="date">08-Feb-2007 02:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Parent methods for callbacks should be called 'parent::method', so if you wish to call a non-static parent method via a callback, you should use a callback of<br />
&lt;?<br />
&nbsp;// always works<br />
&nbsp;$callback = array($this, 'parent::method') <br />
<br />
&nbsp;// works but gives an error in PHP5 with E_STRICT if the parent method is not static<br />
&nbsp;$callback array('parent', 'method'); <br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="72778"></a>
 <div class="note">
  <strong class='user'>Edward</strong>
  <a href="#72778" class="date">01-Feb-2007 02:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To recap mr dot lilov at gmail dot com's comment: If you want to pass a function as an argument to another function, for example "array_map", do this:<br />
<br />
regular functions: <br />
&lt;? <br />
array_map(intval, $array)<br />
?&gt;<br />
<br />
static functions in a class:<br />
&lt;?<br />
array_map(array('MyClass', 'MyFunction'), $array)<br />
?&gt;<br />
<br />
functions from an object:<br />
&lt;?<br />
array_map(array($this, 'MyFunction'), $array)<br />
?&gt;<br />
<br />
I hope this clarifies things a little bit</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.pseudo-types&amp;redirect=http://www.php.net/manual/en/language.pseudo-types.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.pseudo-types&amp;redirect=http://www.php.net/manual/en/language.pseudo-types.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.pseudo-types.php">show source</a> |
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