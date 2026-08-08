<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Array Operators - Manual</title>
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
 <link rel="prev" href="language.operators.string.php" />
 <link rel="next" href="language.operators.type.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.array" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.array.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/language.operators.array.php" />
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
 <li><a href="language.operators.execution.php">Execution Operators</a></li>
 <li><a href="language.operators.increment.php">Incrementing/Decrementing Operators</a></li>
 <li><a href="language.operators.logical.php">Logical Operators</a></li>
 <li><a href="language.operators.string.php">String Operators</a></li>
 <li class="active"><a href="language.operators.array.php">Array Operators</a></li>
 <li><a href="language.operators.type.php">Type Operators</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.operators.type.php">Type Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.string.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />String Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.array.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.array.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.array.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.array.php">French</option>
    <option value="de/language.operators.array.php">German</option>
    <option value="ja/language.operators.array.php">Japanese</option>
    <option value="pl/language.operators.array.php">Polish</option>
    <option value="ro/language.operators.array.php">Romanian</option>
    <option value="ru/language.operators.array.php">Russian</option>
    <option value="fa/language.operators.array.php">Persian</option>
    <option value="es/language.operators.array.php">Spanish</option>
    <option value="tr/language.operators.array.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.array" class="sect1">
   <h2 class="title">Array Operators</h2>
   <table class="doctable table">
    <caption><strong>Array Operators</strong></caption>
    
     <thead>
      <tr>
       <th>Example</th>
       <th>Name</th>
       <th>Result</th>
      </tr>

     </thead>

     <tbody class="tbody">
      <tr>
       <td>$a + $b</td>
       <td>Union</td>
       <td>Union of <var class="varname"><var class="varname">$a</var></var> and <var class="varname"><var class="varname">$b</var></var>.</td>
      </tr>

      <tr>
       <td>$a == $b</td>
       <td>Equality</td>
       <td><strong><code>TRUE</code></strong> if <var class="varname"><var class="varname">$a</var></var> and <var class="varname"><var class="varname">$b</var></var> have the same key/value pairs.</td>
      </tr>

      <tr>
       <td>$a === $b</td>
       <td>Identity</td>
       <td><strong><code>TRUE</code></strong> if <var class="varname"><var class="varname">$a</var></var> and <var class="varname"><var class="varname">$b</var></var> have the same key/value pairs in the same
        order and of the same types.</td>
      </tr>

      <tr>
       <td>$a != $b</td>
       <td>Inequality</td>
       <td><strong><code>TRUE</code></strong> if <var class="varname"><var class="varname">$a</var></var> is not equal to <var class="varname"><var class="varname">$b</var></var>.</td>
      </tr>

      <tr>
       <td>$a &lt;&gt; $b</td>
       <td>Inequality</td>
       <td><strong><code>TRUE</code></strong> if <var class="varname"><var class="varname">$a</var></var> is not equal to <var class="varname"><var class="varname">$b</var></var>.</td>
      </tr>

      <tr>
       <td>$a !== $b</td>
       <td>Non-identity</td>
       <td><strong><code>TRUE</code></strong> if <var class="varname"><var class="varname">$a</var></var> is not identical to <var class="varname"><var class="varname">$b</var></var>.</td>
      </tr>

     </tbody>
    
   </table>

   <p class="para">
    The <em>+</em> operator returns the right-hand array appended
    to the left-hand array; for keys that exist in both arrays, the elements
    from the left-hand array will be used, and the matching elements from the
    right-hand array will be ignored.
   </p>
   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">"a"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"apple"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"b"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"banana"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">"a"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"pear"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"b"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"strawberry"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"c"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"cherry"</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Union&nbsp;of&nbsp;$a&nbsp;and&nbsp;$b<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Union&nbsp;of&nbsp;\$a&nbsp;and&nbsp;\$b:&nbsp;\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$c</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">$c&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$a</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;Union&nbsp;of&nbsp;$b&nbsp;and&nbsp;$a<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"Union&nbsp;of&nbsp;\$b&nbsp;and&nbsp;\$a:&nbsp;\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$c</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    When executed, this script will print the following:
    <div class="example-contents screen">
<div class="cdata"><pre>
Union of $a and $b:
array(3) {
  [&quot;a&quot;]=&gt;
  string(5) &quot;apple&quot;
  [&quot;b&quot;]=&gt;
  string(6) &quot;banana&quot;
  [&quot;c&quot;]=&gt;
  string(6) &quot;cherry&quot;
}
Union of $b and $a:
array(3) {
  [&quot;a&quot;]=&gt;
  string(4) &quot;pear&quot;
  [&quot;b&quot;]=&gt;
  string(10) &quot;strawberry&quot;
  [&quot;c&quot;]=&gt;
  string(6) &quot;cherry&quot;
}
</pre></div>
    </div>
   </p>
   <p class="para">
    Elements of arrays are equal for the comparison if they have the
    same key and value.
   </p>
   <p class="para">
    <div class="example" id="example-123">
     <p><strong>Example #1 Comparing arrays</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">"apple"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"banana"</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"banana"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"0"&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">"apple"</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(true)<br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;bool(false)<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    See also the manual sections on the
    <a href="language.types.array.php" class="link">Array type</a> and
    <a href="ref.array.php" class="link">Array functions</a>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.type.php">Type Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.string.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />String Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.array.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.array&amp;redirect=http://www.php.net/manual/en/language.operators.array.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.array&amp;redirect=http://www.php.net/manual/en/language.operators.array.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Array Operators</strong>
 </div><div id="allnotes">
 <a name="107784"></a>
 <div class="note">
  <strong class='user'>Dan Patrick</strong>
  <a href="#107784" class="date">04-Mar-2012 06:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It should be mentioned that the array union operator functions almost identically to array_replace with the exception that precedence of arguments is reversed.</span>
</code></div>
  </div>
 </div>
 <a name="86379"></a>
 <div class="note">
  <strong class='user'>cb at netalyst dot com</strong>
  <a href="#86379" class="date">15-Oct-2008 11:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The union operator did not behave as I thought it would on first glance. It implements a union (of sorts) based on the keys of the array, not on the values.<br />
<br />
For instance:<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= array(</span><span class="string">'one'</span><span class="keyword">,</span><span class="string">'two'</span><span class="keyword">);<br />
</span><span class="default">$b</span><span class="keyword">=array(</span><span class="string">'three'</span><span class="keyword">,</span><span class="string">'four'</span><span class="keyword">,</span><span class="string">'five'</span><span class="keyword">);<br />
<br />
</span><span class="comment">//not a union of arrays' values<br />
</span><span class="keyword">echo </span><span class="string">'$a + $b : '</span><span class="keyword">;<br />
</span><span class="default">print_r </span><span class="keyword">(</span><span class="default">$a </span><span class="keyword">+ </span><span class="default">$b</span><span class="keyword">);<br />
<br />
</span><span class="comment">//a union of arrays' values<br />
</span><span class="keyword">echo </span><span class="string">"array_unique(array_merge($a,$b)):"</span><span class="keyword">;<br />
</span><span class="comment">// cribbed from <a href="http://oreilly.com/catalog/progphp/chapter/ch05.html" rel="nofollow" target="_blank">http://oreilly.com/catalog/progphp/chapter/ch05.html</a><br />
</span><span class="default">print_r </span><span class="keyword">(</span><span class="default">array_unique</span><span class="keyword">(</span><span class="default">array_merge</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">,</span><span class="default">$b</span><span class="keyword">)));<br />
</span><span class="default">?&gt;<br />
</span><br />
//output<br />
<br />
$a + $b : Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; one<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; two<br />
&nbsp;&nbsp;&nbsp; [2] =&gt; five<br />
)<br />
array_unique(array_merge(Array,Array)):Array<br />
(<br />
&nbsp;&nbsp;&nbsp; [0] =&gt; one<br />
&nbsp;&nbsp;&nbsp; [1] =&gt; two<br />
&nbsp;&nbsp;&nbsp; [2] =&gt; three<br />
&nbsp;&nbsp;&nbsp; [3] =&gt; four<br />
&nbsp;&nbsp;&nbsp; [4] =&gt; five<br />
)</span>
</code></div>
  </div>
 </div>
 <a name="84024"></a>
 <div class="note">
  <strong class='user'>csaba at alum dot mit dot edu</strong>
  <a href="#84024" class="date">24-Jun-2008 08:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple array arithmetic:<br />
A more compact way of adding or subtracting the elements at identical keys...<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">array_add</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">) {&nbsp; </span><span class="comment">// ...<br />
&nbsp; // adds the values at identical keys together<br />
&nbsp; </span><span class="default">$aRes </span><span class="keyword">= </span><span class="default">$a1</span><span class="keyword">;<br />
&nbsp; foreach (</span><span class="default">array_slice</span><span class="keyword">(</span><span class="default">func_get_args</span><span class="keyword">(), </span><span class="default">1</span><span class="keyword">) as </span><span class="default">$aRay</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">array_intersect_key</span><span class="keyword">(</span><span class="default">$aRay</span><span class="keyword">, </span><span class="default">$aRes</span><span class="keyword">) as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] += </span><span class="default">$val</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$aRes </span><span class="keyword">+= </span><span class="default">$aRay</span><span class="keyword">; }<br />
&nbsp; return </span><span class="default">$aRes</span><span class="keyword">; }<br />
<br />
function </span><span class="default">array_subtract</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">) {&nbsp; </span><span class="comment">// ...<br />
&nbsp; // adds the values at identical keys together<br />
&nbsp; </span><span class="default">$aRes </span><span class="keyword">= </span><span class="default">$a1</span><span class="keyword">;<br />
&nbsp; foreach (</span><span class="default">array_slice</span><span class="keyword">(</span><span class="default">func_get_args</span><span class="keyword">(), </span><span class="default">1</span><span class="keyword">) as </span><span class="default">$aRay</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">array_intersect_key</span><span class="keyword">(</span><span class="default">$aRay</span><span class="keyword">, </span><span class="default">$aRes</span><span class="keyword">) as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] -= </span><span class="default">$val</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">array_diff_key</span><span class="keyword">(</span><span class="default">$aRay</span><span class="keyword">, </span><span class="default">$aRes</span><span class="keyword">) as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = -</span><span class="default">$val</span><span class="keyword">; }<br />
&nbsp; return </span><span class="default">$aRes</span><span class="keyword">; }<br />
<br />
</span><span class="default">Example</span><span class="keyword">:<br />
</span><span class="default">$a1 </span><span class="keyword">= array(</span><span class="default">9</span><span class="keyword">, </span><span class="default">8</span><span class="keyword">, </span><span class="default">7</span><span class="keyword">);<br />
</span><span class="default">$a2 </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">=&gt;</span><span class="default">7</span><span class="keyword">, </span><span class="default">6</span><span class="keyword">, </span><span class="default">5</span><span class="keyword">);<br />
</span><span class="default">$a3 </span><span class="keyword">= array(</span><span class="default">2</span><span class="keyword">=&gt;</span><span class="default">5</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">);<br />
<br />
</span><span class="default">$aSum </span><span class="keyword">= </span><span class="default">array_add</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">, </span><span class="default">$a3</span><span class="keyword">);<br />
</span><span class="default">$aDiff </span><span class="keyword">= </span><span class="default">array_subtract</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">, </span><span class="default">$a3</span><span class="keyword">);<br />
<br />
</span><span class="comment">// $aSum&nbsp; =&gt; [9, 15, 18, 9, 3]<br />
// $aDiff =&gt; [9, 1, -4, -9, -3]<br />
</span><span class="default">?&gt;<br />
</span><br />
To make a similar function, array_concatenate(), change only the first of the two '+=' in array_add() to '.='<br />
Csaba Gabor from Vienna</span>
</code></div>
  </div>
 </div>
 <a name="79795"></a>
 <div class="note">
  <strong class='user'>csaba at alum dot mit dot edu</strong>
  <a href="#79795" class="date">13-Dec-2007 01:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Simple array arithmetic:<br />
If you want to add or subtract the elements at identical keys...<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">array_add</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">) {&nbsp; </span><span class="comment">// ...<br />
&nbsp; // adds the values at identical keys together<br />
&nbsp; </span><span class="default">$aRes </span><span class="keyword">= </span><span class="default">$a1</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$aRays </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">();<br />
&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">sizeof</span><span class="keyword">(</span><span class="default">$aRays</span><span class="keyword">);++</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$aRay </span><span class="keyword">= </span><span class="default">$aRays</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$aRay </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$aRes</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] += </span><span class="default">$aRay</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$aRes </span><span class="keyword">+= </span><span class="default">$aRay</span><span class="keyword">; }<br />
&nbsp; return </span><span class="default">$aRes</span><span class="keyword">; }<br />
<br />
function </span><span class="default">array_subtract</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">) {&nbsp; </span><span class="comment">// ...<br />
&nbsp; // subtracts the values at identical keys from the corresponding value in $a1<br />
&nbsp; </span><span class="default">$aRes </span><span class="keyword">= </span><span class="default">$a1</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$aRays </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">();<br />
&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">sizeof</span><span class="keyword">(</span><span class="default">$aRays</span><span class="keyword">);++</span><span class="default">$i</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$aRay </span><span class="keyword">= </span><span class="default">$aRays</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$aRay </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$val</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (</span><span class="default">array_key_exists</span><span class="keyword">(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$aRes</span><span class="keyword">)) </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] -= </span><span class="default">$aRay</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp;&nbsp; else </span><span class="default">$aRes</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = -</span><span class="default">$aRay</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]; } }<br />
&nbsp; return </span><span class="default">$aRes</span><span class="keyword">; }<br />
<br />
</span><span class="default">Example</span><span class="keyword">:<br />
</span><span class="default">$a1 </span><span class="keyword">= array(</span><span class="default">9</span><span class="keyword">, </span><span class="default">8</span><span class="keyword">, </span><span class="default">7</span><span class="keyword">);<br />
</span><span class="default">$a2 </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">=&gt;</span><span class="default">7</span><span class="keyword">, </span><span class="default">6</span><span class="keyword">, </span><span class="default">5</span><span class="keyword">);<br />
</span><span class="default">$a3 </span><span class="keyword">= array(</span><span class="default">2</span><span class="keyword">=&gt;</span><span class="default">5</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">);<br />
<br />
</span><span class="default">$aSum </span><span class="keyword">= </span><span class="default">array_add</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">, </span><span class="default">$a3</span><span class="keyword">);<br />
</span><span class="default">$aDiff </span><span class="keyword">= </span><span class="default">array_subtract</span><span class="keyword">(</span><span class="default">$a1</span><span class="keyword">, </span><span class="default">$a2</span><span class="keyword">, </span><span class="default">$a3</span><span class="keyword">);<br />
<br />
</span><span class="comment">// $aSum&nbsp; =&gt; [9, 15, 18, 9, 3]<br />
// $aDiff =&gt; [9, 1, -4, -9, -3]<br />
</span><span class="default">?&gt;<br />
</span><br />
To make a similar function, array_concatenate(), change the first += only in array_add() to .=<br />
Csaba Gabor from Vienna</span>
</code></div>
  </div>
 </div>
 <a name="74635"></a>
 <div class="note">
  <strong class='user'>Q1712 at online dot ms</strong>
  <a href="#74635" class="date">20-Apr-2007 05:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The example may get u into thinking that the identical operator returns true because the key of apple is a string but that is not the case, cause if a string array key is the standart representation of a integer it's gets a numeral key automaticly. <br />
<br />
The identical operator just requires that the keys are in the same order in both arrays:<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= array (</span><span class="default">0 </span><span class="keyword">=&gt; </span><span class="string">"apple"</span><span class="keyword">, </span><span class="default">1 </span><span class="keyword">=&gt; </span><span class="string">"banana"</span><span class="keyword">);<br />
</span><span class="default">$b </span><span class="keyword">= array (</span><span class="default">1 </span><span class="keyword">=&gt; </span><span class="string">"banana"</span><span class="keyword">, </span><span class="default">0 </span><span class="keyword">=&gt; </span><span class="string">"apple"</span><span class="keyword">);<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a </span><span class="keyword">=== </span><span class="default">$b</span><span class="keyword">); </span><span class="comment">// prints bool(false) as well<br />
<br />
</span><span class="default">$b </span><span class="keyword">= array (</span><span class="string">"0" </span><span class="keyword">=&gt; </span><span class="string">"apple"</span><span class="keyword">, </span><span class="string">"1" </span><span class="keyword">=&gt; </span><span class="string">"banana"</span><span class="keyword">);<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$a </span><span class="keyword">=== </span><span class="default">$b</span><span class="keyword">); </span><span class="comment">// prints bool(true)<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="60865"></a>
 <div class="note">
  <strong class='user'>puneet singh @ value-one dot com</strong>
  <a href="#60865" class="date">18-Jan-2006 10:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
hi&nbsp; just see one more example of union....<br />
<br />
<span class="default">&lt;?php<br />
$a </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">);<br />
</span><span class="default">$b </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">,</span><span class="default">7</span><span class="keyword">,</span><span class="default">8</span><span class="keyword">,</span><span class="default">9</span><span class="keyword">,</span><span class="default">10</span><span class="keyword">);<br />
</span><span class="default">$c </span><span class="keyword">= </span><span class="default">$a </span><span class="keyword">+ </span><span class="default">$b</span><span class="keyword">; </span><span class="comment">// Union of $a and $b<br />
</span><span class="keyword">echo </span><span class="string">"Union of \$a and \$b: \n"</span><span class="keyword">;<br />
</span><span class="comment">//echo $c<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$c</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span> <br />
//output<br />
Union of $a and $b: Array ( [0] =&gt; 1 [1] =&gt; 2 [2] =&gt; 3 [3] =&gt; 9 [4] =&gt; 10 )</span>
</code></div>
  </div>
 </div>
 <a name="56040"></a>
 <div class="note">
  <strong class='user'>kit dot lester at lycos dot co dot uk</strong>
  <a href="#56040" class="date">21-Aug-2005 09:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When comparing arrays that have (some or all) element-values that are themselves array, then in PHP5 it seems that == and === are applied recursively - that is<br />
&nbsp;* two arrays satisfy == if they have the same keys, and the values at each key satisfy == for whatever they happen to be (which might be arrays);<br />
&nbsp;* two arrays satisfy === if they have the same keys, and the values at each key satisfy === for whatever (etc.).<br />
<br />
Which explains what happens if we compare two arrays of arrays of arrays of...<br />
<br />
Likewise, the corresponding inversions for != &lt;&gt; and !==.<br />
<br />
I've tested this to array-of-array-of-array, which seems fairly convincing. I've not tried it in PHP4 or earlier.</span>
</code></div>
  </div>
 </div>
 <a name="56039"></a>
 <div class="note">
  <strong class='user'>kit dot lester at lycos dot co dot uk</strong>
  <a href="#56039" class="date">21-Aug-2005 08:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This manual page doesn't mention &lt; &amp; co for arrays, but example 15-2 in <br />
&nbsp;&nbsp;&nbsp; <a href="@w{6KY7YK6Z}" rel="nofollow" target="_blank">@w{6KY7YK6Z}</a><br />
goes to some lengths to explain how they work.</span>
</code></div>
  </div>
 </div>
 <a name="46987"></a>
 <div class="note">
  <strong class='user'>Peter</strong>
  <a href="#46987" class="date">29-Oct-2004 07:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The code from texbungalow at web dot de below is slightly incorrect.&nbsp; If my memory from primary school history is correct, roman numerals don't allow things like MIM - it has to be MCMXCIX, ie each step is only 1 level down (sorry, I can't explain it very well.<br />
<br />
a print_r($segments) comparing the snippets should explain.<br />
<br />
Corrected code:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">roman </span><span class="keyword">(</span><span class="default">$nr </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$base_digits</span><span class="keyword">= array (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">1</span><span class="keyword">=&gt; </span><span class="string">"I"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">10</span><span class="keyword">=&gt; </span><span class="string">"X"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">100</span><span class="keyword">=&gt; </span><span class="string">"C"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">1000</span><span class="keyword">=&gt; </span><span class="string">"M"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; );<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$help_digits</span><span class="keyword">= array (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">5</span><span class="keyword">=&gt; </span><span class="string">"V"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">50</span><span class="keyword">=&gt; </span><span class="string">"L"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">500</span><span class="keyword">=&gt; </span><span class="string">"D"</span><span class="keyword">, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; );<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$all_digits</span><span class="keyword">= </span><span class="default">$base_digits</span><span class="keyword">+ </span><span class="default">$help_digits</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; foreach (</span><span class="default">$base_digits </span><span class="keyword">as </span><span class="default">$key1</span><span class="keyword">=&gt; </span><span class="default">$value1 </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; foreach (</span><span class="default">$all_digits </span><span class="keyword">as </span><span class="default">$key2</span><span class="keyword">=&gt; </span><span class="default">$value2 </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if (</span><span class="default">$key1</span><span class="keyword">&lt; </span><span class="default">$key2 </span><span class="keyword">&amp;&amp; </span><span class="default">$key1 </span><span class="keyword">&gt;= (</span><span class="default">$key2 </span><span class="keyword">/ </span><span class="default">10</span><span class="keyword">)) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$segments</span><span class="keyword">[</span><span class="default">$key2</span><span class="keyword">- </span><span class="default">$key1 </span><span class="keyword">]= </span><span class="default">$value1</span><span class="keyword">. </span><span class="default">$value2</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$segments</span><span class="keyword">+= </span><span class="default">$all_digits</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">krsort </span><span class="keyword">(</span><span class="default">$segments </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; foreach (</span><span class="default">$segments </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt; </span><span class="default">$value </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; while (</span><span class="default">$key</span><span class="keyword">&lt;= </span><span class="default">$nr </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$nr</span><span class="keyword">-= </span><span class="default">$key</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$str</span><span class="keyword">.= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">$str</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; }<br />
echo </span><span class="default">roman </span><span class="keyword">(</span><span class="default">1998</span><span class="keyword">);&nbsp;&nbsp; </span><span class="comment">//&nbsp; prints MCMXCVIII<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="41811"></a>
 <div class="note">
  <strong class='user'>dfranklin at fen dot com</strong>
  <a href="#41811" class="date">22-Apr-2004 01:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that + will not renumber numeric array keys.&nbsp; If you have two numeric arrays, and their indices overlap, + will use the first array's values for each numeric key, adding the 2nd array's values only where the first doesn't already have a value for that index.&nbsp; Example:<br />
<br />
$a = array('red', 'orange');<br />
$b = array('yellow', 'green', 'blue');<br />
$both = $a + $b;<br />
var_dump($both);<br />
<br />
Produces the output:<br />
<br />
array(3) { [0]=&gt;&nbsp; string(3) "red" [1]=&gt;&nbsp; string(6) "orange" [2]=&gt;&nbsp; string(4) "blue" }<br />
<br />
To get a 5-element array, use array_merge.<br />
<br />
&nbsp;&nbsp;&nbsp; Dan</span>
</code></div>
  </div>
 </div>
 <a name="31584"></a>
 <div class="note">
  <strong class='user'>texbungalow at web dot de</strong>
  <a href="#31584" class="date">26-Apr-2003 06:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
use '+=' to quickly append an array to another one:<br />
<br />
function roman ($nr ) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; $base_digits= array (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 1=&gt; "I", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 10=&gt; "X", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 100=&gt; "C", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 1000=&gt; "M", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp; &nbsp;&nbsp; $help_digits= array (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 5=&gt; "V", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 50=&gt; "L", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 500=&gt; "D", <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp; &nbsp;&nbsp; $all_digits= $base_digits+ $help_digits;<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach ($base_digits as $key1=&gt; $value1 )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; foreach ($all_digits as $key2=&gt; $value2 )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ($key1&lt; $key2 ) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $segments[$key2- $key1 ]= $value1. $value2;<br />
&nbsp;&nbsp; &nbsp;&nbsp; $segments+= $all_digits;<br />
&nbsp;&nbsp; &nbsp;&nbsp; krsort ($segments );<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach ($segments as $key=&gt; $value )<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; while ($key&lt;= $nr ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $nr-= $key;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $str.= $value;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; return $str;<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
<br />
echo roman (888);&nbsp;&nbsp; //&nbsp; prints DCCCLXXXVIII</span>
</code></div>
  </div>
 </div>
 <a name="27536"></a>
 <div class="note">
  <strong class='user'>amirlaher AT yahoo DOT co SPOT uk</strong>
  <a href="#27536" class="date">09-Dec-2002 10:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
[]= could be considered an Array Operator (in the same way that .= is a String Operator). <br />
[]= pushes an element onto the end of an array, similar to array_push:<br />
&lt;? <br />
&nbsp; $array= array(0=&gt;"Amir",1=&gt;"needs");<br />
&nbsp; $array[]= "job";<br />
&nbsp; print_r($array);<br />
?&gt;<br />
Prints: Array ( [0] =&gt; Amir [1] =&gt; needs [2] =&gt; job )</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.array&amp;redirect=http://www.php.net/manual/en/language.operators.array.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.array&amp;redirect=http://www.php.net/manual/en/language.operators.array.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.array.php">show source</a> |
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