<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Object Serialization - Manual</title>
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
 <link rel="index" href="language.oop5.php" />
 <link rel="prev" href="language.oop5.references.php" />
 <link rel="next" href="language.oop5.changelog.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/oop5.serialization" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.oop5.serialization.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{D33X5ZFE}" />
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
 <li class="header up"><a href="language.oop5.php">Classes and Objects</a></li>
 <li><a href="oop5.intro.php">Introduction</a></li>
 <li><a href="language.oop5.basic.php">The Basics</a></li>
 <li><a href="language.oop5.properties.php">Properties</a></li>
 <li><a href="language.oop5.constants.php">Class Constants</a></li>
 <li><a href="language.oop5.autoload.php">Autoloading Classes</a></li>
 <li><a href="language.oop5.decon.php">Constructors and Destructors</a></li>
 <li><a href="language.oop5.visibility.php">Visibility</a></li>
 <li><a href="language.oop5.inheritance.php">Object Inheritance</a></li>
 <li><a href="language.oop5.paamayim-nekudotayim.php">Scope Resolution Operator (::)</a></li>
 <li><a href="language.oop5.static.php">Static Keyword</a></li>
 <li><a href="language.oop5.abstract.php">Class Abstraction</a></li>
 <li><a href="language.oop5.interfaces.php">Object Interfaces</a></li>
 <li><a href="language.oop5.traits.php">Traits</a></li>
 <li><a href="language.oop5.overloading.php">Overloading</a></li>
 <li><a href="language.oop5.iterations.php">Object Iteration</a></li>
 <li><a href="language.oop5.magic.php">Magic Methods</a></li>
 <li><a href="language.oop5.final.php">Final Keyword</a></li>
 <li><a href="language.oop5.cloning.php">Object Cloning</a></li>
 <li><a href="language.oop5.object-comparison.php">Comparing Objects</a></li>
 <li><a href="language.oop5.typehinting.php">Type Hinting</a></li>
 <li><a href="language.oop5.late-static-bindings.php">Late Static Bindings</a></li>
 <li><a href="language.oop5.references.php">Objects and references</a></li>
 <li class="active"><a href="language.oop5.serialization.php">Object Serialization</a></li>
 <li><a href="language.oop5.changelog.php">OOP Changelog</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.changelog.php">OOP Changelog<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.oop5.references.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Objects and references</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.oop5.serialization.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.oop5.serialization.php">Brazilian Portuguese</option>
    <option value="zh/language.oop5.serialization.php">Chinese (Simplified)</option>
    <option value="fr/language.oop5.serialization.php">French</option>
    <option value="de/language.oop5.serialization.php">German</option>
    <option value="ja/language.oop5.serialization.php">Japanese</option>
    <option value="pl/language.oop5.serialization.php">Polish</option>
    <option value="ro/language.oop5.serialization.php">Romanian</option>
    <option value="ru/language.oop5.serialization.php">Russian</option>
    <option value="fa/language.oop5.serialization.php">Persian</option>
    <option value="es/language.oop5.serialization.php">Spanish</option>
    <option value="tr/language.oop5.serialization.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.oop5.serialization" class="sect1">
  <h2 class="title">Object Serialization</h2>
  <h2 class="title">Serializing objects - objects in sessions</h2>

  <p class="para">
    <span class="function"><a href="function.serialize.php" class="function">serialize()</a></span> returns a string containing a
   byte-stream representation of any value that can be stored in
   PHP.  <span class="function"><a href="function.unserialize.php" class="function">unserialize()</a></span> can use this string to
   recreate the original variable values. Using serialize to
   save an object will save all variables in an object.  The
   methods in an object will not be saved, only the name of
   the class.
  </p>
  
  <p class="para">
   In order to be able to  <span class="function"><a href="function.unserialize.php" class="function">unserialize()</a></span> an object, the
   class of that object needs to be defined. That is, if you have an object
    of class A and serialize this, you&#039;ll
   get a string that refers to class A and contains all values of variables
   contained in it. If you want to be able to unserialize
   this in another file, an object of class A, the
   definition of class A must be present in that file first.
   This can be done for example by storing the class definition of class A
   in an include file and including this file or making use of the
    <span class="function"><a href="function.spl-autoload-register.php" class="function">spl_autoload_register()</a></span> function.
  </p>
  
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;classa.inc:<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">A&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;</span><span style="color: #0000BB">$one&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">show_one</span><span style="color: #007700">()&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">one</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;}<br />&nbsp;&nbsp;<br /></span><span style="color: #FF8000">//&nbsp;page1.php:<br /><br />&nbsp;&nbsp;</span><span style="color: #007700">include(</span><span style="color: #DD0000">"classa.inc"</span><span style="color: #007700">);<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">A</span><span style="color: #007700">;<br />&nbsp;&nbsp;</span><span style="color: #0000BB">$s&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">serialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);<br />&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;store&nbsp;$s&nbsp;somewhere&nbsp;where&nbsp;page2.php&nbsp;can&nbsp;find&nbsp;it.<br />&nbsp;&nbsp;</span><span style="color: #0000BB">file_put_contents</span><span style="color: #007700">(</span><span style="color: #DD0000">'store'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$s</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;page2.php:<br />&nbsp;&nbsp;<br />&nbsp;&nbsp;//&nbsp;this&nbsp;is&nbsp;needed&nbsp;for&nbsp;the&nbsp;unserialize&nbsp;to&nbsp;work&nbsp;properly.<br />&nbsp;&nbsp;</span><span style="color: #007700">include(</span><span style="color: #DD0000">"classa.inc"</span><span style="color: #007700">);<br /><br />&nbsp;&nbsp;</span><span style="color: #0000BB">$s&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">file_get_contents</span><span style="color: #007700">(</span><span style="color: #DD0000">'store'</span><span style="color: #007700">);<br />&nbsp;&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">unserialize</span><span style="color: #007700">(</span><span style="color: #0000BB">$s</span><span style="color: #007700">);<br /><br />&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;now&nbsp;use&nbsp;the&nbsp;function&nbsp;show_one()&nbsp;of&nbsp;the&nbsp;$a&nbsp;object.&nbsp;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">show_one</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
  
  <p class="para">
   If an application is using sessions and uses 
    <span class="function"><a href="function.session-register.php" class="function">session_register()</a></span> to register objects, these objects 
   are serialized automatically at the end of each PHP page, and are 
   unserialized automatically on each of the following pages. This means that 
   these objects can show up on any of the application&#039;s pages once they become 
   part of the session. However, the  <span class="function"><a href="function.session-register.php" class="function">session_register()</a></span> is
   removed since PHP 5.4.0.
  </p>
  
  <p class="para">
   It is strongly recommended that if an application serializes objects, for use
   later in the application, that the application includes the class definition
   for that object throughout the application. Not doing so might result in an
   object being unserialized without a class definition, which will result in
   PHP giving the object a class of <strong class="classname">__PHP_Incomplete_Class_Name</strong>,
   which has no methods and would render the object useless.
  </p>
  
  <p class="para">
   So if in the example above <var class="varname"><var class="varname">$a</var></var> became part of a session
   by running <em>session_register(&quot;a&quot;)</em>, you should include the
   file <em>classa.inc</em> on all of your pages, not only <var class="filename">page1.php</var>
   and <var class="filename">page2.php</var>.
  </p>
 </div><br /><br />
<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.oop5.serialization&amp;redirect=@w{D33X5ZFE}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.serialization&amp;redirect=@w{D33X5ZFE}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Object Serialization</strong>
 </div><div id="allnotes">
 <a name="95238"></a>
 <div class="note">
  <strong class='user'>wbcarts at juno dot com</strong>
  <a href="#95238" class="date">20-Dec-2009 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP OBJECT SERIALIZATION<br />
<br />
I use a database to store info rather than storing PHP Objects themselves. However, I find that having a PHP Object acting as an interface to my db is way useful. For example, suppose I have a TABLE called 'user' that looks like this.<br />
<br />
CREATE TABLE user {<br />
&nbsp; user_id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,<br />
&nbsp; user_first VARCHAR(24) NOT NULL,<br />
&nbsp; user_last VARCHAR(24) NOT NULL,<br />
&nbsp; PRIMARY KEY (user_id)<br />
);<br />
<br />
Then I would create a PHP Class definition like so:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">require(</span><span class="string">'includes/db_connect.php'</span><span class="keyword">);<br />
<br />
class </span><span class="default">User<br />
</span><span class="keyword">{<br />
&nbsp; protected </span><span class="default">$user_id</span><span class="keyword">;<br />
&nbsp; protected </span><span class="default">$user_first</span><span class="keyword">;<br />
&nbsp; protected </span><span class="default">$user_last</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$first</span><span class="keyword">, </span><span class="default">$last</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_id </span><span class="keyword">= </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_first </span><span class="keyword">= </span><span class="default">$first</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_last </span><span class="keyword">= </span><span class="default">$last</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment"># FUNCTIONS TO RETRIEVE INFO - DESERIALIZE.<br />
&nbsp; </span><span class="keyword">public static function </span><span class="default">db_user_by_id</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$id</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$query </span><span class="keyword">= </span><span class="string">"SELECT * FROM user WHERE user_id=$id LIMIT 1"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">User</span><span class="keyword">::</span><span class="default">db_select</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">); <br />
&nbsp; }<br />
<br />
&nbsp; public static function </span><span class="default">db_user_by_name</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$first</span><span class="keyword">, </span><span class="default">$last</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$query </span><span class="keyword">= </span><span class="string">"SELECT * FROM user WHERE user_first='$first' AND user_last='$last' LIMIT 1"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">User</span><span class="keyword">::</span><span class="default">db_select</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; protected static function </span><span class="default">db_select</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">);<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">mysqli_query</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">mysqli_num_rows</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">) &gt; </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$row </span><span class="keyword">= </span><span class="default">mysqli_fetch_array</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">MYSQLI_NUM</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; return new </span><span class="default">User</span><span class="keyword">(</span><span class="default">$row</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], </span><span class="default">$row</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">], </span><span class="default">$row</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment"># FUNCTIONS TO SAVE INFO - SERIALIZE.<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">insert</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$query </span><span class="keyword">= </span><span class="string">"INSERT INTO user VALUES (NULL, '$this-&gt;user_first', '$this-&gt;user_last')"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">mysqli_query</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">update</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$query </span><span class="keyword">= </span><span class="string">"UPDATE user SET user_first='$this-&gt;user_first', user_last='$this-&gt;user_last' WHERE user_id=$this-&gt;id LIMIT 1"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">mysqli_query</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">$query</span><span class="keyword">);<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="comment"># GETTER and SETTER FUNCTIONS - DO NOT ALLOW SETTING OF ID<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">getId</span><span class="keyword">() {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_id</span><span class="keyword">;)<br />
&nbsp; public function </span><span class="default">getFirst</span><span class="keyword">() {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_first</span><span class="keyword">;)<br />
&nbsp; public function </span><span class="default">getLast</span><span class="keyword">() {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_last</span><span class="keyword">;)<br />
&nbsp; public function </span><span class="default">setFirst</span><span class="keyword">(</span><span class="default">$first</span><span class="keyword">) {</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_first </span><span class="keyword">= </span><span class="default">$first</span><span class="keyword">;}<br />
&nbsp; public function </span><span class="default">setLast</span><span class="keyword">(</span><span class="default">$last</span><span class="keyword">) {</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_last </span><span class="keyword">= </span><span class="default">$last</span><span class="keyword">;}<br />
<br />
&nbsp; </span><span class="comment"># CUSTOM FUNCTIONS<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">getFullName</span><span class="keyword">() {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_first </span><span class="keyword">. </span><span class="string">' ' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_last</span><span class="keyword">;}<br />
&nbsp; public function </span><span class="default">getLastFirst</span><span class="keyword">() {return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_last </span><span class="keyword">. </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">user_first</span><span class="keyword">;}<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Using PHP Objects for SERIALIZATION and DESERIALIZATION is now super-easy, for example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">require(</span><span class="string">'User.php'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// INSERT a new user.<br />
</span><span class="default">$user </span><span class="keyword">= new </span><span class="default">User</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">, </span><span class="string">'Frank'</span><span class="keyword">, </span><span class="string">'American'</span><span class="keyword">);<br />
</span><span class="default">$user</span><span class="keyword">-&gt;</span><span class="default">insert</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">);&nbsp; </span><span class="comment">// done!<br />
<br />
// UPDATE an existing user.<br />
</span><span class="default">$user </span><span class="keyword">= </span><span class="default">User</span><span class="keyword">::</span><span class="default">db_user_by_id</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">, </span><span class="default">223</span><span class="keyword">);<br />
</span><span class="default">$user</span><span class="keyword">-&gt;</span><span class="default">setFirst</span><span class="keyword">(</span><span class="string">'Johnny'</span><span class="keyword">);<br />
</span><span class="default">$user</span><span class="keyword">-&gt;</span><span class="default">update</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">);&nbsp; </span><span class="comment">// done!<br />
<br />
</span><span class="default">mysqli_close</span><span class="keyword">(</span><span class="default">$dbc</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94139"></a>
 <div class="note">
  <strong class='user'>php at lanar dot com dot au</strong>
  <a href="#94139" class="date">18-Oct-2009 04:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that static members of an object are not serialized.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.oop5.serialization&amp;redirect=@w{D33X5ZFE}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.oop5.serialization&amp;redirect=@w{D33X5ZFE}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.oop5.serialization.php">show source</a> |
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