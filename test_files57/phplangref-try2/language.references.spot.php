<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Spotting References - Manual</title>
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
 <link rel="prev" href="language.references.unset.php" />
 <link rel="next" href="reserved.variables.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/references.spot" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.references.spot.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.references.spot.php" />
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
 <li><a href="language.references.unset.php">Unsetting References</a></li>
 <li class="active"><a href="language.references.spot.php">Spotting References</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="reserved.variables.php">Predefined Variables<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.references.unset.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Unsetting References</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.references.spot.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.references.spot.php">Brazilian Portuguese</option>
    <option value="zh/language.references.spot.php">Chinese (Simplified)</option>
    <option value="fr/language.references.spot.php">French</option>
    <option value="de/language.references.spot.php">German</option>
    <option value="ja/language.references.spot.php">Japanese</option>
    <option value="pl/language.references.spot.php">Polish</option>
    <option value="ro/language.references.spot.php">Romanian</option>
    <option value="ru/language.references.spot.php">Russian</option>
    <option value="fa/language.references.spot.php">Persian</option>
    <option value="es/language.references.spot.php">Spanish</option>
    <option value="tr/language.references.spot.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.references.spot" class="sect1">
   <h2 class="title">Spotting References</h2>
   <p class="simpara">
    Many syntax constructs in PHP are implemented via referencing
    mechanisms, so everything mentioned herein about reference binding also
    applies to these constructs. Some constructs, like passing and
    returning by reference, are mentioned above. Other constructs that
    use references are:
   </p>

   <div class="sect2" id="references.global">
    <h3 class="title">global References</h3>
    <p class="para">
     When you declare a variable as <strong class="command">global $var</strong> you
     are in fact creating reference to a global variable. That means,
     this is the same as:
     <div class="informalexample">
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$var&nbsp;</span><span style="color: #007700">=&amp;&nbsp;</span><span style="color: #0000BB">$GLOBALS</span><span style="color: #007700">[</span><span style="color: #DD0000">"var"</span><span style="color: #007700">];<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
      </div>

     </div>
    </p>
    <p class="simpara">
     This also means that unsetting <var class="varname"><var class="varname">$var</var></var>
     won&#039;t unset the global variable.
    </p>
   </div>

   <div class="sect2" id="references.this">
    <h3 class="title"><em>$this</em></h3>
    <p class="simpara">
     In an object method, <var class="varname"><var class="varname">$this</var></var> is always a reference
     to the caller object.
    </p>
   </div>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="reserved.variables.php">Predefined Variables<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.references.unset.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Unsetting References</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.references.spot.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.references.spot&amp;redirect=http://www.php.net/manual/en/language.references.spot.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.references.spot&amp;redirect=http://www.php.net/manual/en/language.references.spot.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Spotting References</strong>
 </div><div id="allnotes">
 <a name="105203"></a>
 <div class="note">
  <strong class='user'>CodeWorX.ch</strong>
  <a href="#105203" class="date">02-Aug-2011 01:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
here is an unconventional (and not very fast) way of detecting references within arrays:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">is_array_reference </span><span class="keyword">(</span><span class="default">$arr</span><span class="keyword">, </span><span class="default">$key</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$isRef </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">ob_start</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$arr</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">preg_replace</span><span class="keyword">(</span><span class="string">"/[ \n\r]*/i"</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">, </span><span class="default">preg_replace</span><span class="keyword">(</span><span class="string">"/( ){4,}.*(\n\r)*/i"</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">, </span><span class="default">ob_get_contents</span><span class="keyword">())), </span><span class="string">"[" </span><span class="keyword">. </span><span class="default">$key </span><span class="keyword">. </span><span class="string">"]=&gt;&amp;"</span><span class="keyword">) !== </span><span class="default">false</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$isRef </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">ob_end_clean</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$isRef</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="103660"></a>
 <div class="note">
  <strong class='user'>Abimael Rodrguez Coln</strong>
  <a href="#103660" class="date">26-Apr-2011 11:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is one way to check if is a reference<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">2</span><span class="keyword">;<br />
</span><span class="default">$d </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
</span><span class="default">$e </span><span class="keyword">= array(</span><span class="default">$a</span><span class="keyword">);<br />
function </span><span class="default">is_reference</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$val </span><span class="keyword">= </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$var</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$tmpArray </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Add keys/values without reference<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$GLOBALS </span><span class="keyword">as </span><span class="default">$k </span><span class="keyword">=&gt; </span><span class="default">$v</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$v</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$tmpArray</span><span class="keyword">[</span><span class="default">$k</span><span class="keyword">] = </span><span class="default">$v</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Change value of rest variables<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$GLOBALS </span><span class="keyword">as </span><span class="default">$k </span><span class="keyword">=&gt; </span><span class="default">$v</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$k </span><span class="keyword">!= </span><span class="string">'GLOBALS'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; </span><span class="default">$k </span><span class="keyword">!= </span><span class="string">'_POST'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; </span><span class="default">$k </span><span class="keyword">!= </span><span class="string">'_GET'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; </span><span class="default">$k </span><span class="keyword">!= </span><span class="string">'_COOKIE'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; </span><span class="default">$k </span><span class="keyword">!= </span><span class="string">'_FILES'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; </span><span class="default">$k </span><span class="keyword">!= </span><span class="default">$var<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">&amp;&amp; !</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$v</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">usleep</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$k</span><span class="keyword">] = </span><span class="default">md5</span><span class="keyword">(</span><span class="default">microtime</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$bool </span><span class="keyword">= </span><span class="default">$val </span><span class="keyword">!= </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$var</span><span class="keyword">];<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/**<br />
&nbsp;&nbsp; &nbsp; * Restore defaults values<br />
&nbsp;&nbsp; &nbsp; */<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$tmpArray </span><span class="keyword">as </span><span class="default">$k </span><span class="keyword">=&gt; </span><span class="default">$v</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$k</span><span class="keyword">] = </span><span class="default">$v</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$bool</span><span class="keyword">;<br />
}<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_reference</span><span class="keyword">(</span><span class="string">'a'</span><span class="keyword">));<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_reference</span><span class="keyword">(</span><span class="string">'b'</span><span class="keyword">));<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_reference</span><span class="keyword">(</span><span class="string">'c'</span><span class="keyword">));<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_reference</span><span class="keyword">(</span><span class="string">'d'</span><span class="keyword">));<br />
</span><span class="default">?&gt;<br />
</span><br />
This won't check if reference is inside a array.</span>
</code></div>
  </div>
 </div>
 <a name="102177"></a>
 <div class="note">
  <strong class='user'>phpdoc-php at lorgenetwork dot com</strong>
  <a href="#102177" class="date">31-Jan-2011 10:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is a simple function which checks for reference, and works with objects by the test I did. <br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">is_reference_to</span><span class="keyword">(&amp;</span><span class="default">$a</span><span class="keyword">, &amp;</span><span class="default">$b</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$a </span><span class="keyword">!== </span><span class="default">$b</span><span class="keyword">) return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$temp </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$checkval </span><span class="keyword">= (</span><span class="default">$a </span><span class="keyword">=== </span><span class="default">null</span><span class="keyword">)? </span><span class="string">"" </span><span class="keyword">: </span><span class="default">null</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">$checkval</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$b </span><span class="keyword">=== </span><span class="default">$checkval</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">$temp</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="default">$temp</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="72203"></a>
 <div class="note">
  <strong class='user'>BenBE at omorphia dot de</strong>
  <a href="#72203" class="date">07-Jan-2007 11:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hi,<br />
<br />
If you want to check if two variables are referencing each other (i.e. point to the same memory) you can use a function like this:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">same_type</span><span class="keyword">(&amp;</span><span class="default">$var1</span><span class="keyword">, &amp;</span><span class="default">$var2</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">) === </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$var2</span><span class="keyword">);<br />
}<br />
<br />
function </span><span class="default">is_ref</span><span class="keyword">(&amp;</span><span class="default">$var1</span><span class="keyword">, &amp;</span><span class="default">$var2</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//If a reference exists, the type IS the same<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!</span><span class="default">same_type</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">, </span><span class="default">$var2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$same </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//We now only need to ask for var1 to be an array ;-)<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Look for an unused index in $var1<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">do {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$key </span><span class="keyword">= </span><span class="default">uniqid</span><span class="keyword">(</span><span class="string">"is_ref_"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } while(</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$var1</span><span class="keyword">));<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//The two variables differ in content ... They can't be the same<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$var2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//The arrays point to the same data if changes are reflected in $var2<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">uniqid</span><span class="keyword">(</span><span class="string">"is_ref_data_"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$var1</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] =&amp; </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//There seems to be a modification ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$var2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$var2</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] === </span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$same </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Undo our changes ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">unset(</span><span class="default">$var1</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; } elseif(</span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//The same objects are required to have equal class names ;-)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">) !== </span><span class="default">get_class</span><span class="keyword">(</span><span class="default">$var2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$obj1 </span><span class="keyword">= </span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">get_object_vars</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$obj2 </span><span class="keyword">= </span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">get_object_vars</span><span class="keyword">(</span><span class="default">$var2</span><span class="keyword">));<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Look for an unused index in $var1<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">do {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$key </span><span class="keyword">= </span><span class="default">uniqid</span><span class="keyword">(</span><span class="string">"is_ref_"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } while(</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$obj1</span><span class="keyword">));<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//The two variables differ in content ... They can't be the same<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">in_array</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$obj2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//The arrays point to the same data if changes are reflected in $var2<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$data </span><span class="keyword">= </span><span class="default">uniqid</span><span class="keyword">(</span><span class="string">"is_ref_data_"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$var1</span><span class="keyword">-&gt;</span><span class="default">$key </span><span class="keyword">=&amp; </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//There seems to be a modification ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(isset(</span><span class="default">$var2</span><span class="keyword">-&gt;</span><span class="default">$key</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$var2</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] === </span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$same </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Undo our changes ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">unset(</span><span class="default">$var1</span><span class="keyword">-&gt;</span><span class="default">$key</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } elseif (</span><span class="default">is_resource</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">get_resource_type</span><span class="keyword">(</span><span class="default">$var1</span><span class="keyword">) !== </span><span class="default">get_resource_type</span><span class="keyword">(</span><span class="default">$var2</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return ((string) </span><span class="default">$var1</span><span class="keyword">) === ((string) </span><span class="default">$var2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Simple variables ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">$var1</span><span class="keyword">!==</span><span class="default">$var2</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Data mismatch ... They can't be the same ...<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//To check for a reference of a variable with simple type<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; //simply store its old value and check against modifications of the second variable ;-)<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">do {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$key </span><span class="keyword">= </span><span class="default">uniqid</span><span class="keyword">(</span><span class="string">"is_ref_"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } while(</span><span class="default">$key </span><span class="keyword">=== </span><span class="default">$var1</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$tmp </span><span class="keyword">= </span><span class="default">$var1</span><span class="keyword">; </span><span class="comment">//WE NEED A COPY HERE!!!<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$var1 </span><span class="keyword">= </span><span class="default">$key</span><span class="keyword">; </span><span class="comment">//Set var1 to the value of $key (copy)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$same </span><span class="keyword">= </span><span class="default">$var1 </span><span class="keyword">=== </span><span class="default">$var2</span><span class="keyword">; </span><span class="comment">//Check if $var2 was modified too ...<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$var1 </span><span class="keyword">= </span><span class="default">$tmp</span><span class="keyword">; </span><span class="comment">//Undo our changes ...<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$same</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Although this implementation is quite complete, it can't handle function references and some other minor stuff ATM.<br />
This function is especially useful if you want to serialize a recursive array by hand.<br />
<br />
The usage is something like:<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">//false<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">//false<br />
<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="default">5</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">=&amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">//true<br />
</span><span class="keyword">echo </span><span class="string">"---\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$a </span><span class="keyword">= array();<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$a</span><span class="keyword">)); </span><span class="comment">//true<br />
<br />
</span><span class="default">$a</span><span class="keyword">[] =&amp; </span><span class="default">$a</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$a</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">])); </span><span class="comment">// true<br />
</span><span class="keyword">echo </span><span class="string">"---\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= array(array());<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">//true<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">])); </span><span class="comment">//false<br />
</span><span class="keyword">echo </span><span class="string">"---\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$b </span><span class="keyword">= array();<br />
</span><span class="default">$b</span><span class="keyword">[] = </span><span class="default">$b</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">)); </span><span class="comment">//true<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">])); </span><span class="comment">//false<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">is_ref</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">], </span><span class="default">$b</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">][</span><span class="default">0</span><span class="keyword">])); </span><span class="comment">//true*<br />
</span><span class="keyword">echo </span><span class="string">"---\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
* Please note the internal behaviour of PHP that seems to do the reference assignment BEFORE actually copying the variable!!! Thus you get an array containing a (different) recursive array for the last testcase, instead of an array containing an empty array as you could expect.<br />
<br />
BenBE.</span>
</code></div>
  </div>
 </div>
 <a name="62405"></a>
 <div class="note">
  <strong class='user'>ludvig dot ericson at gmail dot com</strong>
  <a href="#62405" class="date">27-Feb-2006 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For the sake of clarity:<br />
<br />
$this is a PSEUDO VARIABLE - thus not a real variable. ZE treats is in other ways then normal variables, and that means that some advanced variable-things won't work on it (for obvious reasons):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Test </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$monkeys </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">doFoobar</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$var </span><span class="keyword">= </span><span class="string">"this"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $</span><span class="default">$var</span><span class="keyword">-&gt;</span><span class="default">monkeys</span><span class="keyword">++; </span><span class="comment">// Will fail on this line.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">Test</span><span class="keyword">;<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">doFoobar</span><span class="keyword">(); </span><span class="comment">// Will fail in this call.<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">monkeys</span><span class="keyword">); </span><span class="comment">// Will return int(0) if it even reaches here.<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="61744"></a>
 <div class="note">
  <strong class='user'>ksamvel at gmail dot com</strong>
  <a href="#61744" class="date">10-Feb-2006 09:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One may check reference to any object by simple operator==( object). Example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{}<br />
<br />
&nbsp; </span><span class="default">$oA1 </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
<br />
&nbsp; </span><span class="default">$roA </span><span class="keyword">= &amp; </span><span class="default">$oA1</span><span class="keyword">;<br />
<br />
&nbsp; echo </span><span class="string">"roA and oA1 are " </span><span class="keyword">. ( </span><span class="default">$roA </span><span class="keyword">== </span><span class="default">$oA1 </span><span class="keyword">? </span><span class="string">"same" </span><span class="keyword">: </span><span class="string">"not same"</span><span class="keyword">) . </span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="default">$oA2 </span><span class="keyword">= new </span><span class="default">A</span><span class="keyword">();<br />
&nbsp; </span><span class="default">$roA </span><span class="keyword">= &amp; </span><span class="default">$roA2</span><span class="keyword">;<br />
<br />
&nbsp; echo </span><span class="string">"roA and oA1 are " </span><span class="keyword">. ( </span><span class="default">$roA </span><span class="keyword">== </span><span class="default">$oA1 </span><span class="keyword">? </span><span class="string">"same" </span><span class="keyword">: </span><span class="string">"not same"</span><span class="keyword">) . </span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Output:<br />
<br />
roA and oA1 are same<br />
roA and oA1 are not same<br />
<br />
Current technique might be useful for caching in objects when inheritance is used and only base part of extended class should be copied (analog of C++: oB = oA):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{ <br />
&nbsp; </span><span class="comment">/* Any function changing state of A should set $bChanged to true */<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">isChanged</span><span class="keyword">() { return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bChanged</span><span class="keyword">; }<br />
&nbsp; private </span><span class="default">$bChanged</span><span class="keyword">;<br />
&nbsp; </span><span class="comment">//...<br />
</span><span class="keyword">}<br />
<br />
class </span><span class="default">B </span><span class="keyword">extends </span><span class="default">A </span><span class="keyword">{<br />
</span><span class="comment">// ...<br />
&nbsp; </span><span class="keyword">public function </span><span class="default">set</span><span class="keyword">( &amp;</span><span class="default">$roObj</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if( </span><span class="default">$roObj </span><span class="keyword">instanceof </span><span class="default">A</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if( </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">roAObj </span><span class="keyword">== </span><span class="default">$roObj </span><span class="keyword">&amp;&amp;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$roObj</span><span class="keyword">-&gt;</span><span class="default">isChanged</span><span class="keyword">()) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">/* Object was not changed do not need to copy A part of B */<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">} else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">/* Copy A part of B */<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">roAObj </span><span class="keyword">= &amp;</span><span class="default">$roObj</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; private </span><span class="default">$roAObj</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="59820"></a>
 <div class="note">
  <strong class='user'>Sergio Santana: ssantana at tlaloc dot imta dot mx</strong>
  <a href="#59820" class="date">16-Dec-2005 08:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
*** WARNING about OBJECTS TRICKY REFERENCES ***<br />
-----------------------------------------------<br />
The use of references in the context of classes<br />
and objects, though well defined in the documentation,<br />
is somehow tricky, so one must be very careful when<br />
using objects. Let's examine the following two<br />
examples:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">y </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$d</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; </span><span class="default">$A </span><span class="keyword">= new </span><span class="default">y</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$A</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="default">18</span><span class="keyword">;<br />
&nbsp; echo </span><span class="string">"Object \$A before operation:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">);<br />
&nbsp; <br />
&nbsp; </span><span class="default">$B </span><span class="keyword">= </span><span class="default">$A</span><span class="keyword">; </span><span class="comment">// This is not an explicit (=&amp;) reference assignment,<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; // however, $A and $B refer to the same instance <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; // though they are not equivalent names<br />
&nbsp; </span><span class="default">$C </span><span class="keyword">=&amp; </span><span class="default">$A</span><span class="keyword">; </span><span class="comment">// Explicit reference assignment, $A and $C refer to <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // the same instance and they have become equivalent<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // names of the same instance<br />
&nbsp; <br />
&nbsp; </span><span class="default">$B</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="default">1234</span><span class="keyword">;<br />
&nbsp;<br />
&nbsp; echo </span><span class="string">"\nObject \$B after operation:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$B</span><span class="keyword">);<br />
&nbsp; echo </span><span class="string">"\nObject \$A implicitly modified after operation:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">); <br />
&nbsp; echo </span><span class="string">"\nObject \$C implicitly modified after operation:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$C</span><span class="keyword">); <br />
&nbsp; <br />
&nbsp; </span><span class="comment">// Let's make $A refer to another instance<br />
&nbsp; </span><span class="default">$A </span><span class="keyword">= new </span><span class="default">y</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$A</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="default">25200</span><span class="keyword">;<br />
&nbsp; echo </span><span class="string">"\nObject \$B after \$A modification:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$B</span><span class="keyword">); </span><span class="comment">// $B doesn't change<br />
&nbsp; </span><span class="keyword">echo </span><span class="string">"\nObject \$A after \$A modification:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">); <br />
&nbsp; echo </span><span class="string">"\nObject \$C implicitly modified after \$A modification:\n"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$C</span><span class="keyword">); </span><span class="comment">// $C changes as $A changes<br />
</span><span class="default">?&gt;<br />
</span><br />
Thus, note the difference between assignments $X = $Y and $X =&amp; $Y.<br />
When $Y is anything but an object instance, the first assignment means<br />
that $X will hold an independent copy of $Y, and the second, means that<br />
$X and $Y will refer to the same thing, so they are tight together until <br />
either $X or $Y is forced to refer to another thing. However, when $Y <br />
happens to be an object instance, the semantic of $X = $Y changes and <br />
becomes only slightly different to that of $X =&amp; $Y, since in both<br />
cases $X and $Y become references to the same object. See what this<br />
example outputs:<br />
<br />
Object $A before operation:<br />
object(y)#1 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(18)<br />
}<br />
<br />
Object $B after operation:<br />
object(y)#1 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(1234)<br />
}<br />
<br />
Object $A implicitly modified after operation:<br />
object(y)#1 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(1234)<br />
}<br />
<br />
Object $C implicitly modified after operation:<br />
object(y)#1 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(1234)<br />
}<br />
<br />
Object $B after $A modification:<br />
object(y)#1 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(1234)<br />
}<br />
<br />
Object $A after $A modification:<br />
object(y)#2 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(25200)<br />
}<br />
<br />
Object $C implicitly modified after $A modification:<br />
object(y)#2 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(25200)<br />
}<br />
<br />
Let's review a SECOND EXAMPLE:<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="keyword">class </span><span class="default">yy </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$d</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">yy</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="default">$x</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
&nbsp; function </span><span class="default">modify</span><span class="keyword">(</span><span class="default">$v</span><span class="keyword">)<br />
&nbsp; {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$v</span><span class="keyword">-&gt;</span><span class="default">d </span><span class="keyword">= </span><span class="default">1225</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
&nbsp; </span><span class="default">$A </span><span class="keyword">= new </span><span class="default">yy</span><span class="keyword">(</span><span class="default">3</span><span class="keyword">);<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">); <br />
&nbsp; </span><span class="default">modify</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">);<br />
&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$A</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Although, in general, a formal argument declared <br />
as $v in the function 'modify' shown above, implies<br />
that the actual argument $A, passed when calling <br />
the function, is not modified, this is not the <br />
case when $A is an object instance. See what the<br />
example code outputs when executed:<br />
<br />
object(yy)#3 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(3)<br />
}<br />
object(yy)#3 (1) {<br />
&nbsp; ["d"]=&gt;<br />
&nbsp; int(1225)<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="55689"></a>
 <div class="note">
  <strong class='user'>Sergio Santana: ssantana at tlaloc dot imta dot mx</strong>
  <a href="#55689" class="date">10-Aug-2005 09:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Sometimes an object's method returning a reference to itself is required. Here is a way to code it:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp; public </span><span class="default">$datum</span><span class="keyword">;<br />
&nbsp; public </span><span class="default">$other</span><span class="keyword">;<br />
&nbsp; <br />
&nbsp; function &amp;</span><span class="default">MyRef</span><span class="keyword">(</span><span class="default">$d</span><span class="keyword">) { </span><span class="comment">// the method<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">datum </span><span class="keyword">= </span><span class="default">$d</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">; </span><span class="comment">// returns the reference<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">;<br />
</span><span class="default">$b </span><span class="keyword">= </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">MyRef</span><span class="keyword">(</span><span class="default">25</span><span class="keyword">); </span><span class="comment">// creates the reference<br />
<br />
</span><span class="keyword">echo </span><span class="string">"This is object \$a: \n"</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
echo </span><span class="string">"This is object \$b: \n"</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$b</span><span class="keyword">);<br />
<br />
</span><span class="default">$b</span><span class="keyword">-&gt;</span><span class="default">other </span><span class="keyword">= </span><span class="default">50</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"This is object \$a, modified" </span><span class="keyword">.<br />
&nbsp;&nbsp; &nbsp; </span><span class="string">" indirectly by modifying ref \$b: \n"</span><span class="keyword">;<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
This code outputs:<br />
This is object $a:<br />
MyClass Object<br />
(<br />
&nbsp;&nbsp;&nbsp; [datum] =&gt; 25<br />
&nbsp;&nbsp;&nbsp; [other] =&gt;<br />
)<br />
This is object $b:<br />
MyClass Object<br />
(<br />
&nbsp;&nbsp;&nbsp; [datum] =&gt; 25<br />
&nbsp;&nbsp;&nbsp; [other] =&gt;<br />
)<br />
This is object $a, modified indirectly by modifying ref $b:<br />
MyClass Object<br />
(<br />
&nbsp;&nbsp;&nbsp; [datum] =&gt; 25<br />
&nbsp;&nbsp;&nbsp; [other] =&gt; 50<br />
)</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.references.spot&amp;redirect=http://www.php.net/manual/en/language.references.spot.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.references.spot&amp;redirect=http://www.php.net/manual/en/language.references.spot.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.references.spot.php">show source</a> |
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