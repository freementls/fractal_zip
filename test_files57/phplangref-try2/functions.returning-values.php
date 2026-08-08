<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Returning values - Manual</title>
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
 <link rel="index" href="language.functions.php" />
 <link rel="prev" href="functions.arguments.php" />
 <link rel="next" href="functions.variable-functions.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions.returning-values" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/functions.returning-values.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{CZZYWGCQ}" />
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
 <li class="header up"><a href="language.functions.php">Functions</a></li>
 <li><a href="functions.user-defined.php">User-defined functions</a></li>
 <li><a href="functions.arguments.php">Function arguments</a></li>
 <li class="active"><a href="functions.returning-values.php">Returning values</a></li>
 <li><a href="functions.variable-functions.php">Variable functions</a></li>
 <li><a href="functions.internal.php">Internal (built-in) functions</a></li>
 <li><a href="functions.anonymous.php">Anonymous functions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="functions.variable-functions.php">Variable functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.arguments.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Function arguments</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.returning-values.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/functions.returning-values.php">Brazilian Portuguese</option>
    <option value="zh/functions.returning-values.php">Chinese (Simplified)</option>
    <option value="fr/functions.returning-values.php">French</option>
    <option value="de/functions.returning-values.php">German</option>
    <option value="ja/functions.returning-values.php">Japanese</option>
    <option value="pl/functions.returning-values.php">Polish</option>
    <option value="ro/functions.returning-values.php">Romanian</option>
    <option value="ru/functions.returning-values.php">Russian</option>
    <option value="fa/functions.returning-values.php">Persian</option>
    <option value="es/functions.returning-values.php">Spanish</option>
    <option value="tr/functions.returning-values.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="functions.returning-values" class="sect1">
   <h2 class="title">Returning values</h2>
 
   <p class="para">
    Values are returned by using the optional return statement. Any
    type may be returned, including arrays and objects. This causes the
    function to end its execution immediately and pass control back to
    the line from which it was called. See  <span class="function"><a href="function.return.php" class="function">return</a></span>
    for more information.
   </p>
   <blockquote class="note"><p><strong class="note">Note</strong>: 
    <p class="para">
     If the  <span class="function"><a href="function.return.php" class="function">return</a></span> is omitted the value <strong><code>NULL</code></strong> will be
     returned.
    </p>
   </p></blockquote>
   <p class="para">
    <div class="example" id="example-155">
     <p><strong>Example #1 Use of  <span class="function"><a href="function.return.php" class="function">return</a></span></strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">square</span><span style="color: #007700">(</span><span style="color: #0000BB">$num</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$num&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">$num</span><span style="color: #007700">;<br />}<br />echo&nbsp;</span><span style="color: #0000BB">square</span><span style="color: #007700">(</span><span style="color: #0000BB">4</span><span style="color: #007700">);&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;outputs&nbsp;'16'.<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
      
   <p class="para">
    A function can not return multiple values, but similar results can be
    obtained by returning an array.
   </p>
   <p class="para">
    <div class="example" id="example-156">
     <p><strong>Example #2 Returning an array to get multiple values</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">small_numbers</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;array&nbsp;(</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">);<br />}<br />list&nbsp;(</span><span style="color: #0000BB">$zero</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$one</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$two</span><span style="color: #007700">)&nbsp;=&nbsp;</span><span style="color: #0000BB">small_numbers</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    To return a reference from a function, use the reference operator &amp; in
    both the function declaration and when assigning the returned value to a
    variable:
   </p>
   <p class="para">
    <div class="example" id="example-157">
     <p><strong>Example #3 Returning a reference from a function</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">function&nbsp;&amp;</span><span style="color: #0000BB">returns_reference</span><span style="color: #007700">()<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$someref</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$newref&nbsp;</span><span style="color: #007700">=&amp;&nbsp;</span><span style="color: #0000BB">returns_reference</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="simpara">
    For more information on references, please check out <a href="language.references.php" class="link">References Explained</a>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="functions.variable-functions.php">Variable functions<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.arguments.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Function arguments</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.returning-values.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=functions.returning-values&amp;redirect=@w{CZZYWGCQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.returning-values&amp;redirect=@w{CZZYWGCQ}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Returning values</strong>
 </div><div id="allnotes">
 <a name="109378"></a>
 <div class="note">
  <strong class='user'>toron_mail at yahoo dot com</strong>
  <a href="#109378" class="date">11-Jul-2012 09:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I used recursive to reverse a string, created code as below<br />
<span class="default">&lt;?php<br />
$str</span><span class="keyword">=</span><span class="string">"abcdef"</span><span class="keyword">;<br />
function </span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; static </span><span class="default">$retStr</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$str</span><span class="keyword">==</span><span class="default">NULL </span><span class="keyword">|| </span><span class="default">$str</span><span class="keyword">==</span><span class="string">""</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$retStr</span><span class="keyword">; </span><span class="comment">// here the $retStr="fedcba", correct<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">} else { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$retStr </span><span class="keyword">.= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">, -</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$str </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, -</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
echo </span><span class="string">"\nThe return string = "</span><span class="keyword">.</span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">).</span><span class="string">"\n"</span><span class="keyword">;&nbsp; </span><span class="comment">// the return string is empty<br />
</span><span class="default">?&gt;<br />
</span><br />
if I changed the code, use global, like below<br />
<span class="default">&lt;?php<br />
$str</span><span class="keyword">=</span><span class="string">"abcdef"</span><span class="keyword">;<br />
</span><span class="default">$retStr </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">;<br />
function </span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$retStr</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$str</span><span class="keyword">==</span><span class="default">NULL </span><span class="keyword">|| </span><span class="default">$str</span><span class="keyword">==</span><span class="string">""</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">NULL</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$retStr </span><span class="keyword">.= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">, -</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$str </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, -</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">convert</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">);<br />
echo </span><span class="string">"\nThe global = "</span><span class="keyword">.</span><span class="default">$retStr</span><span class="keyword">; </span><span class="comment">// get the right result "fedcba"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="101805"></a>
 <div class="note">
  <strong class='user'>bbraun at basicbusinesssim dot com</strong>
  <a href="#101805" class="date">12-Jan-2011 03:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Variables declared as global inside a function are available outside the function.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">writefunctionvars</span><span class="keyword">() {<br />
<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$foo</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"something"</span><span class="keyword">;<br />
<br />
}<br />
<br />
</span><span class="default">writefunctionvars</span><span class="keyword">();<br />
echo </span><span class="default">$foo</span><span class="keyword">; </span><span class="comment">// displays "something"<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99660"></a>
 <div class="note">
  <strong class='user'>rstaveley at seseit dot com</strong>
  <a href="#99660" class="date">29-Aug-2010 01:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Developers with a C background may expect pass by reference semantics for arrays. It may be surprising that&nbsp; pass by value is used for arrays just like scalars. Objects are implicitly passed by reference.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment"># (1) Objects are always passed by reference and returned by reference<br />
<br />
</span><span class="keyword">class </span><span class="default">Obj </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$x</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">obj_inc_x</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">x</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$obj</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">Obj</span><span class="keyword">();<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$obj2 </span><span class="keyword">= </span><span class="default">obj_inc_x</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">);<br />
</span><span class="default">obj_inc_x</span><span class="keyword">(</span><span class="default">$obj2</span><span class="keyword">);<br />
<br />
print </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">. </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$obj2</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment"># (2) Scalars are not passed by reference or returned as such<br />
<br />
</span><span class="keyword">function </span><span class="default">scalar_inc_x</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$x</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$x</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$x </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$x2 </span><span class="keyword">= </span><span class="default">scalar_inc_x</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">);<br />
</span><span class="default">scalar_inc_x</span><span class="keyword">(</span><span class="default">$x2</span><span class="keyword">);<br />
<br />
print </span><span class="default">$x </span><span class="keyword">. </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$x2 </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment"># (3) You have to force pass by reference and return by reference on scalars<br />
<br />
</span><span class="keyword">function &amp;</span><span class="default">scalar_ref_inc_x</span><span class="keyword">(&amp;</span><span class="default">$x</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$x</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$x</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$x </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$x2 </span><span class="keyword">=&amp; </span><span class="default">scalar_ref_inc_x</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">);&nbsp; &nbsp; </span><span class="comment"># Need reference here as well as the function sig<br />
</span><span class="default">scalar_ref_inc_x</span><span class="keyword">(</span><span class="default">$x2</span><span class="keyword">);<br />
<br />
print </span><span class="default">$x </span><span class="keyword">. </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$x2 </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment"># (4) Arrays use pass by value sematics just like scalars<br />
<br />
</span><span class="keyword">function </span><span class="default">array_inc_x</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$array</span><span class="keyword">{</span><span class="string">'x'</span><span class="keyword">}++;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$array</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$array </span><span class="keyword">= array();<br />
</span><span class="default">$array</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] = </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$array2 </span><span class="keyword">= </span><span class="default">array_inc_x</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">);<br />
</span><span class="default">array_inc_x</span><span class="keyword">(</span><span class="default">$array2</span><span class="keyword">);<br />
<br />
print </span><span class="default">$array</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] . </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$array2</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] . </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment"># (5) You have to force pass by reference and return by reference on arrays<br />
<br />
</span><span class="keyword">function &amp;</span><span class="default">array_ref_inc_x</span><span class="keyword">(&amp;</span><span class="default">$array</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$array</span><span class="keyword">{</span><span class="string">'x'</span><span class="keyword">}++;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$array</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$array </span><span class="keyword">= array();<br />
</span><span class="default">$array</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] = </span><span class="default">1</span><span class="keyword">;<br />
<br />
</span><span class="default">$array2 </span><span class="keyword">=&amp; </span><span class="default">array_ref_inc_x</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">); </span><span class="comment"># Need reference here as well as the function sig<br />
</span><span class="default">array_ref_inc_x</span><span class="keyword">(</span><span class="default">$array2</span><span class="keyword">);<br />
<br />
print </span><span class="default">$array</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] . </span><span class="string">', ' </span><span class="keyword">. </span><span class="default">$array2</span><span class="keyword">[</span><span class="string">'x'</span><span class="keyword">] . </span><span class="string">"\n"</span><span class="keyword">;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98839"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#98839" class="date">09-Jul-2010 07:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As of at least PHP 5.3, a function or class method returning an object acts like an object.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">A </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Yay!"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
function </span><span class="default">get_obj</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">A</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">get_obj</span><span class="keyword">()-&gt;</span><span class="default">test</span><span class="keyword">();&nbsp; </span><span class="comment">// "Yay!"<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Sorry, still doesn't work with arrays.&nbsp; Ie <span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">get_array</span><span class="keyword">()[</span><span class="default">1</span><span class="keyword">]; </span><span class="default">?&gt;</span> fails.</span>
</code></div>
  </div>
 </div>
 <a name="82116"></a>
 <div class="note">
  <strong class='user'>bgalloway at citycarshare dot org</strong>
  <a href="#82116" class="date">27-Mar-2008 06:27</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful about using "do this thing or die()" logic in your return lines.&nbsp; It doesn't work as you'd expect:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">myfunc1</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return(</span><span class="string">'thingy' </span><span class="keyword">or die(</span><span class="string">'otherthingy'</span><span class="keyword">));<br />
}<br />
function </span><span class="default">myfunc2</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'thingy' </span><span class="keyword">or die(</span><span class="string">'otherthingy'</span><span class="keyword">);<br />
}<br />
function </span><span class="default">myfunc3</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return(</span><span class="string">'thingy'</span><span class="keyword">) or die(</span><span class="string">'otherthingy'</span><span class="keyword">);<br />
}<br />
function </span><span class="default">myfunc4</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">'thingy' </span><span class="keyword">or </span><span class="string">'otherthingy'</span><span class="keyword">;<br />
}<br />
function </span><span class="default">myfunc5</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$x </span><span class="keyword">= </span><span class="string">'thingy' </span><span class="keyword">or </span><span class="string">'otherthingy'</span><span class="keyword">; return </span><span class="default">$x</span><span class="keyword">;<br />
}<br />
echo </span><span class="default">myfunc1</span><span class="keyword">(). </span><span class="string">"\n"</span><span class="keyword">. </span><span class="default">myfunc2</span><span class="keyword">(). </span><span class="string">"\n"</span><span class="keyword">. </span><span class="default">myfunc3</span><span class="keyword">(). </span><span class="string">"\n"</span><span class="keyword">. </span><span class="default">myfunc4</span><span class="keyword">(). </span><span class="string">"\n"</span><span class="keyword">. </span><span class="default">myfunc5</span><span class="keyword">(). </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Only myfunc5() returns 'thingy' - the rest return 1.</span>
</code></div>
  </div>
 </div>
 <a name="64576"></a>
 <div class="note">
  <strong class='user'>Trevor Blackbird &gt; yurab.com</strong>
  <a href="#64576" class="date">18-Apr-2006 04:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also use the compact-extract pair to return multiple values:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">Composite</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">, </span><span class="default">$y</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$sum </span><span class="keyword">= </span><span class="default">$x </span><span class="keyword">+ </span><span class="default">$y</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$dif </span><span class="keyword">= </span><span class="default">$x </span><span class="keyword">- </span><span class="default">$y</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">compact</span><span class="keyword">(</span><span class="string">'sum'</span><span class="keyword">, </span><span class="string">'dif'</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">extract</span><span class="keyword">(</span><span class="default">Composite</span><span class="keyword">(</span><span class="default">3</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">));<br />
echo </span><span class="default">$sum</span><span class="keyword">;<br />
echo </span><span class="default">$dif</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="34686"></a>
 <div class="note">
  <strong class='user'>nick at itomic.com</strong>
  <a href="#34686" class="date">04-Aug-2003 12:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Functions which return references, may return a NULL value. This is inconsistent with the fact that function parameters passed by reference can't be passed as NULL (or in fact anything which isnt a variable).<br />
<br />
i.e.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function &amp;</span><span class="default">testRet</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">NULL</span><span class="keyword">;<br />
}<br />
<br />
if (</span><span class="default">testRet</span><span class="keyword">() === </span><span class="default">NULL</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"NULL"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
parses fine and echoes NULL</span>
</code></div>
  </div>
 </div>
 <a name="34180"></a>
 <div class="note">
  <strong class='user'>rusty at socrates dot berkeley dot edu</strong>
  <a href="#34180" class="date">17-Jul-2003 02:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a sick idea.&nbsp; When a function returns no value, if you assign its return value to a variable that variable will be unset.&nbsp; So instead of returning -1 on error just return with no value.<br />
<br />
For example,<br />
<br />
function myfunc($myvar) {<br />
&nbsp; if ($myvar == "abc")<br />
&nbsp;&nbsp;&nbsp; return(1);<br />
<br />
&nbsp; if ($myvar == "xyz")<br />
&nbsp;&nbsp;&nbsp; return(2);<br />
<br />
&nbsp; return;<br />
}<br />
<br />
$abc = myfunc("def");<br />
<br />
if (isset($abc))<br />
&nbsp;&nbsp; echo("a-ok");<br />
else<br />
&nbsp; echo("oops");</span>
</code></div>
  </div>
 </div>
 <a name="30677"></a>
 <div class="note">
  <strong class='user'>LouisGreen at pljg dot freeserve dot co dot uk</strong>
  <a href="#30677" class="date">25-Mar-2003 10:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It seems that when you wish to export a varible, you can do it as return $varible, return an array(), or globalise it. If you return something, information for that varible can only travel one way when the script is running, and that is out of the function. <br />
<br />
function fn() {<br />
&nbsp;&nbsp; $varible = "something";<br />
<br />
&nbsp; return $varible;<br />
}<br />
<br />
echo fn();<br />
OR<br />
$newvarible = fn();<br />
<br />
Although if global was used, it creates a pointer to a varible, whether it existed or not, and makes whatever is created in the function linked to that global pointer. So if the pointer was global $varible, and then you set a value to $varible, it would then be accessible in the global scope. But then what if you later on in the script redefine that global to equal something else. This means that whatever is put into the global array, the information that is set in the pointer, can be set at any point (overiden). Here is an example that might make this a little clearer:<br />
<br />
function fn1() {<br />
<br />
&nbsp;&nbsp; global $varible; // Pointer to the global array<br />
&nbsp;&nbsp; $varible = "something";<br />
}<br />
<br />
fn1();<br />
echo $varible; // Prints something<br />
$varible = "12345";<br />
echo $varible; // Prints 12345<br />
<br />
function fn2() {<br />
<br />
&nbsp;&nbsp; global $varible; // Pointer to the global array<br />
&nbsp;&nbsp; echo $varible;<br />
}<br />
<br />
fn2(); // echos $varible which contains "12345"<br />
<br />
Basically with the global array, you can set it refer to something already defined or set it to something, (a pointer) such as varible you plan to create in the function, and later possibly over ride the pointer with something else.</span>
</code></div>
  </div>
 </div>
 <a name="28506"></a>
 <div class="note">
  <strong class='user'>ian at NO_SPAM dot verteron dot net</strong>
  <a href="#28506" class="date">15-Jan-2003 04:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In reference to the poster above, an additional (better?) way to return multiple values from a function is to use list(). For example:<br />
<br />
function fn($a, $b)<br />
{<br />
&nbsp;&nbsp; # complex stuff<br />
<br />
&nbsp;&nbsp; return array(<br />
&nbsp;&nbsp; &nbsp;&nbsp; $a * $b,<br />
&nbsp;&nbsp; &nbsp;&nbsp; $a + $b,<br />
&nbsp;&nbsp; );<br />
}<br />
<br />
list($product, $sum) = fn(3, 4);<br />
<br />
echo $product; # prints 12<br />
echo $sum; # prints 7</span>
</code></div>
  </div>
 </div>
 <a name="22672"></a>
 <div class="note">
  <strong class='user'>devinemke at yahoo dot com</strong>
  <a href="#22672" class="date">26-Jun-2002 11:45</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A function can only return one value, but that value can be an array or other compound value.&nbsp; If you want to just define several variables into the global scope within your function you can do two things:<br />
<br />
1. return an array from your function and then run the extract() function<br />
<br />
$result_array = test ();<br />
extract ($result_array);<br />
<br />
2. Or you can just append the variables to the $GLOBALS array:<br />
<br />
$array = array ('first' =&gt; 'john', 'middle' =&gt; 'q', 'last' =&gt; 'public');<br />
function upper_case () {<br />
global $array;<br />
foreach ($array as $key =&gt; $value)<br />
{<br />
$GLOBALS[$key] = strtoupper ($value);<br />
}<br />
}<br />
<br />
upper_case ();<br />
echo "$first $middle $last";<br />
// returns JOHN Q PUBLIC<br />
<br />
In this second example you can create multiple values without necessarily returning anything from the function.&nbsp; This may be handy for applying several functions (stripslashes, trim, etc..) accross all elements of $_POST or $_GET and then having all of the newly cleaned up variables extracted out for you.</span>
</code></div>
  </div>
 </div>
 <a name="19557"></a>
 <div class="note">
  <strong class='user'>destes at ix dot netcom dot com dot nospam</strong>
  <a href="#19557" class="date">03-Mar-2002 12:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One thing to note about returning arrays- the usual "complex variable" syntax will give you a parse error.&nbsp; For instance, if you have:<br />
<br />
function adminstuff() {<br />
&nbsp;&nbsp;&nbsp; mysql_connect('localhost', 'user', 'pass');<br />
&nbsp;&nbsp;&nbsp; mysql_select_db('mydb');<br />
&nbsp;&nbsp;&nbsp; $result = mysql_query('SELECT login, pass FROM users);<br />
&nbsp;&nbsp;&nbsp; $resultrow = mysql_fetch_array( $result );<br />
<br />
&nbsp;&nbsp;&nbsp; # this next line is key:<br />
&nbsp;&nbsp;&nbsp; return array ({$resultrow['login']}, {$resultrow['pass']});<br />
}<br />
<br />
That won't work.&nbsp; You *can* reference array elements without the curly-braces syntax, i.e.:<br />
<br />
return array ($resultrow['login'], $resultrow['pass']);<br />
<br />
But you'll get a parse error if you try to use curly braces.&nbsp; Thanks,<br />
<br />
Steve</span>
</code></div>
  </div>
 </div>
 <a name="12623"></a>
 <div class="note">
  <strong class='user'>php at control-escape dot com</strong>
  <a href="#12623" class="date">25-Apr-2001 12:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP functions that do not explicitly return a value will be 'void', that is, they return 'null'. C programmers will be accustomed to this already, but folks coming from Perl may expect the return value of a function to be the return value of the last expression evaluated inside the function, as is the case with Perl. Not so.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=functions.returning-values&amp;redirect=@w{CZZYWGCQ}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.returning-values&amp;redirect=@w{CZZYWGCQ}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/functions.returning-values.php">show source</a> |
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