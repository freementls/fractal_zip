<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Unsetting References - Manual</title>
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
 <link rel="index" href="language.references.php" />
 <link rel="prev" href="language.references.return.php" />
 <link rel="next" href="language.references.spot.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/references.unset" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.references.unset.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.references.unset.php" />
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
 <li class="header up"><a href="language.references.php">References Explained</a></li>
 <li><a href="language.references.whatare.php">What References Are</a></li>
 <li><a href="language.references.whatdo.php">What References Do</a></li>
 <li><a href="language.references.arent.php">What References Are Not</a></li>
 <li><a href="language.references.pass.php">Passing by Reference</a></li>
 <li><a href="language.references.return.php">Returning References</a></li>
 <li class="active"><a href="language.references.unset.php">Unsetting References</a></li>
 <li><a href="language.references.spot.php">Spotting References</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.references.spot.php">Spotting References<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.references.return.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Returning References</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.references.unset.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.references.unset.php">Brazilian Portuguese</option>
    <option value="zh/language.references.unset.php">Chinese (Simplified)</option>
    <option value="fr/language.references.unset.php">French</option>
    <option value="de/language.references.unset.php">German</option>
    <option value="ja/language.references.unset.php">Japanese</option>
    <option value="pl/language.references.unset.php">Polish</option>
    <option value="ro/language.references.unset.php">Romanian</option>
    <option value="ru/language.references.unset.php">Russian</option>
    <option value="fa/language.references.unset.php">Persian</option>
    <option value="es/language.references.unset.php">Spanish</option>
    <option value="tr/language.references.unset.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.references.unset" class="sect1">
   <h2 class="title">Unsetting References</h2>
   <p class="para">
    When you unset the reference, you just break the binding between
    variable name and variable content. This does not mean that
    variable content will be destroyed. For example:
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&amp;&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;<br />unset(</span><span style="color: #0000BB">$a</span><span style="color: #007700">);&nbsp;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    won&#039;t unset <var class="varname"><var class="varname">$b</var></var>, just <var class="varname"><var class="varname">$a</var></var>. 
   </p>
   <p class="simpara">
    Again, it might be useful to think about this as analogous to the Unix
    <strong class="command">unlink</strong> call.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.references.spot.php">Spotting References<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.references.return.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Returning References</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.references.unset.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.references.unset&amp;redirect=http://www.php.net/manual/en/language.references.unset.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.references.unset&amp;redirect=http://www.php.net/manual/en/language.references.unset.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Unsetting References</strong>
 </div><div id="allnotes">
 <a name="105845"></a>
 <div class="note">
  <strong class='user'>lowlight1974 at gmail dot com</strong>
  <a href="#105845" class="date">19-Sep-2011 05:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unsetting array and objects does NOT work when trying to unset them.&nbsp; You have to iterate through them in order to empty them out, akin to cleaning out memory in c/c++.&nbsp; Here are some examples:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">something </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
echo </span><span class="string">"Running test(\$a)\n"</span><span class="keyword">;<br />
</span><span class="default">test</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="comment">//At this point $a still has the something=true values<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
<br />
echo </span><span class="string">"\nRunning nuke_me(\$a)\n"</span><span class="keyword">;<br />
</span><span class="default">nuke_me</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="comment">//At this point, the values in $a have been deleted.<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="string">"&lt;/pre&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="comment">//Now, one would think that $a would be unset after running test().&nbsp; But in this instance, $a is UNTOUCHED.<br />
<br />
//Call $a by reference, name it something else for giggles<br />
</span><span class="keyword">function </span><span class="default">test</span><span class="keyword">(&amp;</span><span class="default">$trash</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp; unset(</span><span class="default">$trash</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">nuke_me</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp; if (</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">) || </span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">))<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$var </span><span class="keyword">AS </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; if (isset(</span><span class="default">$key</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; unset(</span><span class="default">$var</span><span class="keyword">-&gt;</span><span class="default">$key</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; unset(</span><span class="default">$var</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; else<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; unset(</span><span class="default">$var</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="82955"></a>
 <div class="note">
  <strong class='user'>ojars26 at NOSPAM dot inbox dot lv</strong>
  <a href="#82955" class="date">03-May-2008 07:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple look how PHP Reference works<br />
<span class="default">&lt;?php<br />
</span><span class="comment">/* Imagine this is memory map<br />
&nbsp;______________________________<br />
|pointer | value | variable&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |<br />
&nbsp;-----------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
------------------------------------<br />
Create some variables&nbsp;&nbsp; */<br />
</span><span class="default">$a</span><span class="keyword">=</span><span class="default">10</span><span class="keyword">;<br />
</span><span class="default">$b</span><span class="keyword">=</span><span class="default">20</span><span class="keyword">;<br />
</span><span class="default">$c</span><span class="keyword">=array (</span><span class="string">'one'</span><span class="keyword">=&gt;array (</span><span class="default">1</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">));<br />
</span><span class="comment">/* Look at memory<br />
&nbsp;_______________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;-----------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; 10&nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; $a&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; 20&nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; $b&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][2]&nbsp;&nbsp; |<br />
------------------------------------<br />
do&nbsp; */<br />
</span><span class="default">$a</span><span class="keyword">=&amp;</span><span class="default">$c</span><span class="keyword">[</span><span class="string">'one'</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">];<br />
</span><span class="comment">/* Look at memory<br />
&nbsp;_______________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;-----------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; //value of&nbsp; $a is destroyed and pointer is free<br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; 20&nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; $b&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; $c['one'][2]&nbsp; ,$a | // $a is now here<br />
------------------------------------<br />
do&nbsp; */<br />
</span><span class="default">$b</span><span class="keyword">=&amp;</span><span class="default">$a</span><span class="keyword">;&nbsp; </span><span class="comment">// or&nbsp; $b=&amp;$c['one'][2]; result is same as both "$c['one'][2]" and "$a" is at same pointer.<br />
/* Look at memory<br />
&nbsp;_________________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;--------------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; //value of&nbsp; $b is destroyed and pointer is free<br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |$c['one'][2]&nbsp; ,$a , $b |&nbsp; // $b is now here<br />
---------------------------------------<br />
next do */<br />
</span><span class="keyword">unset(</span><span class="default">$c</span><span class="keyword">[</span><span class="string">'one'</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">]);<br />
</span><span class="comment">/* Look at memory<br />
&nbsp;_________________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;--------------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $a , $b&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; | // $c['one'][2]&nbsp; is&nbsp; destroyed not in memory, not in array<br />
---------------------------------------<br />
next do&nbsp;&nbsp; */<br />
</span><span class="default">$c</span><span class="keyword">[</span><span class="string">'one'</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">]=</span><span class="default">500</span><span class="keyword">;&nbsp; &nbsp; </span><span class="comment">//now it is in array<br />
/* Look at memory<br />
&nbsp;_________________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;--------------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; 500&nbsp; &nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][2]&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; //created it lands on any(next) free pointer in memory<br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $a , $b&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; | //this pointer is in use<br />
---------------------------------------<br />
lets tray to return $c['one'][2] at old pointer an remove reference $a,$b.&nbsp; */<br />
</span><span class="default">$c</span><span class="keyword">[</span><span class="string">'one'</span><span class="keyword">][</span><span class="default">2</span><span class="keyword">]=&amp;</span><span class="default">$a</span><span class="keyword">;<br />
unset(</span><span class="default">$a</span><span class="keyword">);<br />
unset(</span><span class="default">$b</span><span class="keyword">);&nbsp;&nbsp; <br />
</span><span class="comment">/* look at memory<br />
&nbsp;_________________________________<br />
|pointer | value |&nbsp; &nbsp; &nbsp;&nbsp; variable's&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; |<br />
&nbsp;--------------------------------------<br />
|&nbsp;&nbsp; 1&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 2&nbsp; &nbsp;&nbsp; |&nbsp; NULL&nbsp; |&nbsp; &nbsp; &nbsp;&nbsp; ---&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; |&nbsp; <br />
|&nbsp;&nbsp; 3&nbsp; &nbsp;&nbsp; |&nbsp; 1&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][0]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 4&nbsp; &nbsp;&nbsp; |&nbsp; 2&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][1]&nbsp; &nbsp; &nbsp;&nbsp; |<br />
|&nbsp;&nbsp; 5&nbsp; &nbsp;&nbsp; |&nbsp; 3&nbsp; &nbsp; &nbsp;&nbsp; |&nbsp; &nbsp; &nbsp; $c['one'][2]&nbsp; &nbsp; &nbsp;&nbsp; | //$c['one'][2] is returned, $a,$b is destroyed<br />
--------------------------------------- ?&gt;<br />
I hope this helps.</span>
</span>
</code></div>
  </div>
 </div>
 <a name="72945"></a>
 <div class="note">
  <strong class='user'>sony-santos at bol dot com dot br</strong>
  <a href="#72945" class="date">05-Feb-2007 03:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">//if you do:<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="string">"eita"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$c</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// shows "eita"<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="string">"eita"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$c</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// shows "hihaha"<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
echo </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// shows nothing (both are set to null)<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
unset(</span><span class="default">$b</span><span class="keyword">);<br />
echo </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// shows "hihaha"<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="string">"eita"</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">$c</span><span class="keyword">;<br />
echo </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// shows "eita"<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="string">"eita"</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= &amp;</span><span class="default">$c</span><span class="keyword">;<br />
echo </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// shows "hihaha"<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
echo </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// shows nothing (both are set to null)<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"hihaha"</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp;</span><span class="default">$a</span><span class="keyword">;<br />
unset(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// shows "hihaha"<br />
</span><span class="default">?&gt;<br />
</span><br />
I tested each case individually on PHP 4.3.10.</span>
</code></div>
  </div>
 </div>
 <a name="72162"></a>
 <div class="note">
  <strong class='user'>martin</strong>
  <a href="#72162" class="date">05-Jan-2007 04:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
note that in the previous example all variables (or the one data item all variables point to) is set to NULL, what is interpreted as !isset(), but the linkage between the variables still exists, so<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo (isset(</span><span class="default">$a</span><span class="keyword">)?</span><span class="string">"set"</span><span class="keyword">:</span><span class="string">"unset"</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">;<br />
echo (isset(</span><span class="default">$b</span><span class="keyword">)?</span><span class="string">"set"</span><span class="keyword">:</span><span class="string">"unset"</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">=</span><span class="default">null</span><span class="keyword">;<br />
echo (isset(</span><span class="default">$b</span><span class="keyword">)?</span><span class="string">"set"</span><span class="keyword">:</span><span class="string">"unset"</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">$a</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;<br />
echo (isset(</span><span class="default">$b</span><span class="keyword">)?</span><span class="string">"set"</span><span class="keyword">:</span><span class="string">"unset"</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
shows:<br />
unset<br />
set<br />
unset<br />
set<br />
<br />
note that $b ist set again.<br />
<br />
So if you want to brake the linkage, you have to use unset()</span>
</code></div>
  </div>
 </div>
 <a name="69429"></a>
 <div class="note">
  <strong class='user'>lazer_erazer</strong>
  <a href="#69429" class="date">05-Sep-2006 04:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Your idea about unsetting all referenced variables at once is right,<br />
just a tiny note that you changed NULL with unset()...<br />
again, unset affects only one name and NULL affects the data,<br />
which is kept by all the three names...<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; <br />
</span><span class="default">?&gt;<br />
</span><br />
This does also work!<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">=&amp; </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; <br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="68109"></a>
 <div class="note">
  <strong class='user'>donny at semeleer dot nl</strong>
  <a href="#68109" class="date">13-Jul-2006 07:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's an example of unsetting a reference without losing an ealier set reference<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="string">'Bob'</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// Assign the value 'Bob' to $foo<br />
</span><span class="default">$bar </span><span class="keyword">= &amp;</span><span class="default">$foo</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// Reference $foo via $bar.<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="string">"My name is $bar"</span><span class="keyword">;&nbsp; </span><span class="comment">// Alter $bar...<br />
</span><span class="keyword">echo </span><span class="default">$bar</span><span class="keyword">;<br />
echo </span><span class="default">$foo</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// $foo is altered too.<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"I am Frank"</span><span class="keyword">;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Alter $foo and $bar because of the reference<br />
</span><span class="keyword">echo </span><span class="default">$bar</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// output: I am Frank<br />
</span><span class="keyword">echo </span><span class="default">$foo</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// output: I am Frank<br />
<br />
</span><span class="default">$foobar </span><span class="keyword">= &amp;</span><span class="default">$bar</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// create a new reference between $foobar and $bar<br />
</span><span class="default">$foobar </span><span class="keyword">= </span><span class="string">"hello $foobar"</span><span class="keyword">; </span><span class="comment">// alter $foobar and with that $bar and $foo<br />
</span><span class="keyword">echo </span><span class="default">$foobar</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//output : hello I am Frank<br />
</span><span class="keyword">unset(</span><span class="default">$bar</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// unset $bar and destroy the reference<br />
</span><span class="default">$bar </span><span class="keyword">= </span><span class="string">"dude!"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// assign $bar<br />
/* even though the reference between $bar and $foo is destroyed, and also the <br />
reference between $bar and $foobar is destroyed, there is still a reference <br />
between $foo and $foobar. */<br />
</span><span class="keyword">echo </span><span class="default">$foo</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// output : hello I am Frank<br />
</span><span class="keyword">echo </span><span class="default">$bar</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// output : due!<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="61039"></a>
 <div class="note">
  <strong class='user'>libi</strong>
  <a href="#61039" class="date">24-Jan-2006 12:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
clerca at inp-net dot eu dot org<br />
"<br />
If you have a lot of references linked to the same contents, maybe it could be useful to do this : <br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= &amp; </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// $a, $b, $c reference the same content '1'<br />
<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; </span><span class="comment">// All variables $a, $b or $c are unset<br />
</span><span class="default">?&gt;<br />
</span><br />
"<br />
<br />
------------------------<br />
<br />
NULL will not result in unseting the variables.<br />
Its only change the value to "null" for all the variables.<br />
becouse they all points to the same "part" in the memory.</span>
</code></div>
  </div>
 </div>
 <a name="59104"></a>
 <div class="note">
  <strong class='user'>clerca at inp-net dot eu dot org</strong>
  <a href="#59104" class="date">26-Nov-2005 12:33</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you have a lot of references linked to the same contents, maybe it could be useful to do this : <br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= &amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= &amp; </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// $a, $b, $c reference the same content '1'<br />
<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">NULL</span><span class="keyword">; </span><span class="comment">// All variables $a, $b or $c are unset<br />
</span><span class="default">?&gt;<br />
</span><br />
I haven't test this trick a lot, but well, it seems to work greatly.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.references.unset&amp;redirect=http://www.php.net/manual/en/language.references.unset.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.references.unset&amp;redirect=http://www.php.net/manual/en/language.references.unset.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.references.unset.php">show source</a> |
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