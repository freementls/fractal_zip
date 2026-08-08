<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: do-while - Manual</title>
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
 <link rel="prev" href="control-structures.while.php" />
 <link rel="next" href="control-structures.for.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/do.while" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.do.while.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{5W5NXTGD}" />
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
 <li class="active"><a href="control-structures.do.while.php">do-while</a></li>
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
 <li><a href="function.include-once.php">include_<span class="w"> </span>once</a></li>
 <li><a href="control-structures.goto.php">goto</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="control-structures.for.php">for<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.while.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />while</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.do.while.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.do.while.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.do.while.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.do.while.php">French</option>
    <option value="de/control-structures.do.while.php">German</option>
    <option value="ja/control-structures.do.while.php">Japanese</option>
    <option value="pl/control-structures.do.while.php">Polish</option>
    <option value="ro/control-structures.do.while.php">Romanian</option>
    <option value="ru/control-structures.do.while.php">Russian</option>
    <option value="fa/control-structures.do.while.php">Persian</option>
    <option value="es/control-structures.do.while.php">Spanish</option>
    <option value="tr/control-structures.do.while.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.do.while" class="sect1">
 <h2 class="title"><em>do-while</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="simpara">
  <em>do-while</em> loops are very similar to
  <em>while</em> loops, except the truth expression is
  checked at the end of each iteration instead of in the beginning.
  The main difference from regular <em>while</em> loops is
  that the first iteration of a <em>do-while</em> loop is
  guaranteed to run (the truth expression is only checked at the end
  of the iteration), whereas it may not necessarily run with a
  regular <em>while</em> loop (the truth expression is
  checked at the beginning of each iteration, if it evaluates to
  <strong><code>FALSE</code></strong> right from the beginning, the loop
  execution would end immediately).
 </p>
 <p class="para">
  There is just one syntax for <em>do-while</em> loops:

  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />do&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$i</span><span style="color: #007700">;<br />}&nbsp;while&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&gt;&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
   The above loop would run one time exactly, since after the first
   iteration, when truth expression is checked, it evaluates to
   <strong><code>FALSE</code></strong> (<var class="varname"><var class="varname">$i</var></var> is not bigger than 0) and the loop
   execution ends.
 </p>
 <p class="para">
  Advanced C users may be familiar with a different usage of the
  <em>do-while</em> loop, to allow stopping execution in
  the middle of code blocks, by encapsulating them with
  <em>do-while</em> (0), and using the <a href="control-structures.break.php" class="link"><em>break</em></a>
  statement.  The following code fragment demonstrates this:
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">do&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"i&nbsp;is&nbsp;not&nbsp;big&nbsp;enough"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">*=&nbsp;</span><span style="color: #0000BB">$factor</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">$minimum_limit</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"i&nbsp;is&nbsp;ok"</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;process&nbsp;i&nbsp;*/<br /><br /></span><span style="color: #007700">}&nbsp;while&nbsp;(</span><span style="color: #0000BB">0</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="simpara">
  Don&#039;t worry if you don&#039;t understand this right away or at all.
  You can code scripts and even powerful scripts without using this
  &#039;feature&#039;.
  Since PHP 5.3.0, it is possible to use
  <a href="control-structures.goto.php" class="link"><em>goto</em></a>
  operator instead of this hack.
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.for.php">for<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.while.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />while</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.do.while.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.do.while&amp;redirect=@w{5W5NXTGD}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.do.while&amp;redirect=@w{5W5NXTGD}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>do-while</strong>
 </div><div id="allnotes">
 <a name="102985"></a>
 <div class="note">
  <strong class='user'>david dot schueler at tel-billig dot de</strong>
  <a href="#102985" class="date">18-Mar-2011 05:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are trying to use a construct like this:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">do {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// some code to run only one more time if expression is true<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$your_expression </span><span class="keyword">== </span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
} while (</span><span class="default">false</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>It will NOT loop as expected, because the continue tries to run the "next" loop, but the expression says just "false" so there is no next loop. The continue exits the while loop.<br />
To get around this you may use an other expression, like this<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">do {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// some code to run only one more time if expression is true<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$your_expression </span><span class="keyword">== </span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
&nbsp;&nbsp;&nbsp; break;<br />
} while (</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>or use the goto statement since PHP 5.3.</span>
</code></div>
  </div>
 </div>
 <a name="102897"></a>
 <div class="note">
  <strong class='user'>xiaomao5 at live dot com</strong>
  <a href="#102897" class="date">13-Mar-2011 07:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another hack<br />
If you want $type to only have a value of 0 or 1, you can do this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">do {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'Choose a type, 0 or 1: '</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$type </span><span class="keyword">= </span><span class="default">trim</span><span class="keyword">(</span><span class="default">fgets</span><span class="keyword">(</span><span class="default">STDIN</span><span class="keyword">));<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$type </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">/* do stuff */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">} elseif (</span><span class="default">$type </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">/* do stuff */&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
} while (</span><span class="default">$type </span><span class="keyword">!= </span><span class="default">0 </span><span class="keyword">&amp;&amp; </span><span class="default">$type </span><span class="keyword">!= </span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99468"></a>
 <div class="note">
  <strong class='user'>shaida dot mca at gmail dot com</strong>
  <a href="#99468" class="date">18-Aug-2010 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example of Do while :-<br />
<br />
<span class="default">&lt;?php<br />
$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
echo </span><span class="string">'This code will run at least once because i default value is 0.&lt;br/&gt;'</span><span class="keyword">;<br />
do {<br />
echo </span><span class="string">'i value is ' </span><span class="keyword">. </span><span class="default">$i </span><span class="keyword">. </span><span class="string">', so code block will run. &lt;br/&gt;'</span><span class="keyword">;<br />
++</span><span class="default">$i</span><span class="keyword">;<br />
} while (</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85739"></a>
 <div class="note">
  <strong class='user'>andrew at NOSPAM dot devohive dot com</strong>
  <a href="#85739" class="date">15-Sep-2008 10:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I'm guilty of writing constructs without curly braces sometimes... writing the do--while seemed a bit odd without the curly braces ({ and }), but just so everyone is aware of how this is written with a do--while...<br />
<br />
a normal while:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="keyword">while ( </span><span class="default">$isValid </span><span class="keyword">) </span><span class="default">$isValid </span><span class="keyword">= </span><span class="default">doSomething</span><span class="keyword">(</span><span class="default">$input</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
a do--while:<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="keyword">do </span><span class="default">$isValid </span><span class="keyword">= </span><span class="default">doSomething</span><span class="keyword">(</span><span class="default">$input</span><span class="keyword">);<br />
&nbsp;&nbsp; while ( </span><span class="default">$isValid </span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Also, a practical example of when to use a do--while when a simple while just won't do (lol)... copying multiple 2nd level nodes from one document to another using the DOM XML extension<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="comment"># open up/create the documents and grab the root element<br />
&nbsp;&nbsp; </span><span class="default">$fileDoc&nbsp; </span><span class="keyword">= </span><span class="default">domxml_open_file</span><span class="keyword">(</span><span class="string">'example.xml'</span><span class="keyword">); </span><span class="comment">// existing xml we want to copy<br />
&nbsp;&nbsp; </span><span class="default">$fileRoot </span><span class="keyword">= </span><span class="default">$fileDoc</span><span class="keyword">-&gt;</span><span class="default">document_element</span><span class="keyword">();<br />
&nbsp;&nbsp; </span><span class="default">$newDoc&nbsp;&nbsp; </span><span class="keyword">= </span><span class="default">domxml_new_doc</span><span class="keyword">(</span><span class="string">'1.0'</span><span class="keyword">); </span><span class="comment">// new document we want to copy to<br />
&nbsp;&nbsp; </span><span class="default">$newRoot&nbsp; </span><span class="keyword">= </span><span class="default">$newDoc</span><span class="keyword">-&gt;</span><span class="default">create_element</span><span class="keyword">(</span><span class="string">'rootnode'</span><span class="keyword">);<br />
&nbsp;&nbsp; </span><span class="default">$newRoot&nbsp; </span><span class="keyword">= </span><span class="default">$newDoc</span><span class="keyword">-&gt;</span><span class="default">append_child</span><span class="keyword">(</span><span class="default">$newRoot</span><span class="keyword">); </span><span class="comment">// this is the node we want to copy to<br />
<br />
&nbsp;&nbsp; # loop through nodes and clone (using deep)<br />
&nbsp;&nbsp; </span><span class="default">$child </span><span class="keyword">= </span><span class="default">$fileRoot</span><span class="keyword">-&gt;</span><span class="default">first_child</span><span class="keyword">(); </span><span class="comment">// first_child must be called once and can only be called once<br />
&nbsp;&nbsp; </span><span class="keyword">do </span><span class="default">$newRoot</span><span class="keyword">-&gt;</span><span class="default">append_child</span><span class="keyword">(</span><span class="default">$child</span><span class="keyword">-&gt;</span><span class="default">clone_node</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">)); </span><span class="comment">// do first, so that the result from first_child is appended<br />
&nbsp;&nbsp; </span><span class="keyword">while ( </span><span class="default">$child </span><span class="keyword">= </span><span class="default">$child</span><span class="keyword">-&gt;</span><span class="default">next_sibling</span><span class="keyword">() ); </span><span class="comment">// we have to use next_sibling for everything after first_child<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="82608"></a>
 <div class="note">
  <strong class='user'>Ryan</strong>
  <a href="#82608" class="date">17-Apr-2008 10:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I've found that the most useful thing to use do-while loops for is multiple checks of file existence. The guaranteed iteration means that it will check through at least once, which I had trouble with using a simple "while" loop because it never incremented at the end.<br />
<br />
My code was:<br />
<br />
<span class="default">&lt;?php<br />
$filename </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">"."</span><span class="keyword">, </span><span class="default">$_FILES</span><span class="keyword">[</span><span class="string">'file'</span><span class="keyword">][</span><span class="string">'name'</span><span class="keyword">]); </span><span class="comment">// File being uploaded<br />
</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="comment">// Number of times processed (number to add at the end of the filename)<br />
</span><span class="keyword">do {<br />
&nbsp; </span><span class="comment">/* Since most files being uploaded don't end with a number,<br />
&nbsp;&nbsp; &nbsp;&nbsp; we have to make sure that there is a number at the end<br />
&nbsp;&nbsp; &nbsp;&nbsp; of the filename before we start simply incrementing. I<br />
&nbsp;&nbsp; &nbsp;&nbsp; admit there is probably an easier way to do this, but this<br />
&nbsp;&nbsp; &nbsp;&nbsp; was a quick slap-together job for a friend, and I find it<br />
&nbsp;&nbsp; &nbsp;&nbsp; works just fine. So, the first part "if($i &gt; 0) ..." says that<br />
&nbsp;&nbsp; &nbsp;&nbsp; if the loop has already been run at least once, then there<br />
&nbsp;&nbsp; &nbsp;&nbsp; is now a number at the end of the filename and we can <br />
&nbsp;&nbsp; &nbsp;&nbsp; simply increment that. Otherwise, we have to place a<br />
&nbsp;&nbsp; &nbsp;&nbsp; number at the end of the filename, which is where $i<br />
&nbsp;&nbsp; &nbsp;&nbsp; comes in even handier */<br />
<br />
&nbsp; </span><span class="keyword">if(</span><span class="default">$i </span><span class="keyword">&gt; </span><span class="default">0</span><span class="keyword">) </span><span class="default">$filename</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]++;<br />
&nbsp; else </span><span class="default">$filename</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] = </span><span class="default">$filename</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">].</span><span class="default">$i</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
} while(</span><span class="default">file_exists</span><span class="keyword">(</span><span class="string">"uploaded/"</span><span class="keyword">.</span><span class="default">$filename</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">].</span><span class="string">"."</span><span class="keyword">.</span><span class="default">$filename</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]));<br />
<br />
</span><span class="comment">/* Now that everything is uploaded, we should move it<br />
&nbsp;&nbsp;&nbsp; somewhere it can be accessed. Hence, the "uploaded"<br />
&nbsp;&nbsp;&nbsp; folder. */<br />
</span><span class="default">move_uploaded_file</span><span class="keyword">(</span><span class="default">$_FILES</span><span class="keyword">[</span><span class="string">'file'</span><span class="keyword">][</span><span class="string">'tmp_name'</span><span class="keyword">], </span><span class="string">"uploaded/"</span><span class="keyword">.</span><span class="default">$filename</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">].</span><span class="string">"."</span><span class="keyword">.</span><span class="default">$filename</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]);<br />
</span><span class="default">?&gt;<br />
</span><br />
I'm sure there are plenty of ways of doing this without using the do-while loop, but I managed to toss this one together in no-time flat, and I'm not a great PHP programmer. =) It's simple and effective, and I personally think it works better than any "for" or "while" loop that I've seen that does the same thing.</span>
</code></div>
  </div>
 </div>
 <a name="79470"></a>
 <div class="note">
  <strong class='user'>jantsch at gmail dot com</strong>
  <a href="#79470" class="date">28-Nov-2007 05:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Useful when you want to continue to read a recordset that was already being read like in:<br />
<br />
&lt;?<br />
$sql = "select * from customers";<br />
$res = mysql_query( $sql );<br />
<br />
// read the first record<br />
if( $rs = mysql_fetch_row( $res ) ){<br />
&nbsp;&nbsp; // do something with this record<br />
<br />
}<br />
<br />
// do another stuff here<br />
<br />
// keep reading till the end<br />
if( mysql_num_rows( $res )&gt;1 ){<br />
&nbsp;&nbsp; do{<br />
&nbsp;&nbsp; &nbsp;&nbsp; // processing the records till the end<br />
<br />
&nbsp;&nbsp; }while( $rs = mysql_fetch_row( $res ));<br />
<br />
}<br />
<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="74419"></a>
 <div class="note">
  <strong class='user'>jayreardon at gmail dot com</strong>
  <a href="#74419" class="date">10-Apr-2007 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is one major difference you should be aware of when using the do--while loop vs. using a simple while loop:&nbsp; And that is when the check condition is made.&nbsp; <br />
<br />
In a do--while loop, the test condition evaluation is at the end of the loop.&nbsp; This means that the code inside of the loop will iterate once through before the condition is ever evaluated.&nbsp; This is ideal for tasks that need to execute once before a test is made to continue, such as test that is dependant upon the results of the loop.&nbsp; <br />
<br />
Conversely, a plain while loop evaluates the test condition at the begining of the loop before any execution in the loop block is ever made. If for some reason your test condition evaluates to false at the very start of the loop, none of the code inside your loop will be executed.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.do.while&amp;redirect=@w{5W5NXTGD}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.do.while&amp;redirect=@w{5W5NXTGD}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.do.while.php">show source</a> |
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