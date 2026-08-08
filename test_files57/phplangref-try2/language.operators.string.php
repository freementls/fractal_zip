<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: String Operators - Manual</title>
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
 <link rel="prev" href="language.operators.logical.php" />
 <link rel="next" href="language.operators.array.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/operators.string" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.operators.string.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{GYKCBYFH}" />
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
 <li class="active"><a href="language.operators.string.php">String Operators</a></li>
 <li><a href="language.operators.array.php">Array Operators</a></li>
 <li><a href="language.operators.type.php">Type Operators</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.operators.array.php">Array Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.logical.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Logical Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.string.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.operators.string.php">Brazilian Portuguese</option>
    <option value="zh/language.operators.string.php">Chinese (Simplified)</option>
    <option value="fr/language.operators.string.php">French</option>
    <option value="de/language.operators.string.php">German</option>
    <option value="ja/language.operators.string.php">Japanese</option>
    <option value="pl/language.operators.string.php">Polish</option>
    <option value="ro/language.operators.string.php">Romanian</option>
    <option value="ru/language.operators.string.php">Russian</option>
    <option value="fa/language.operators.string.php">Persian</option>
    <option value="es/language.operators.string.php">Spanish</option>
    <option value="tr/language.operators.string.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.operators.string" class="sect1">
   <h2 class="title">String Operators</h2>
   <p class="simpara">
    There are two <span class="type"><a href="language.types.string.php" class="type string">string</a></span> operators. The first is the
    concatenation operator (&#039;.&#039;), which returns the concatenation of its
    right and left arguments. The second is the concatenating assignment
    operator (&#039;<em>.=</em>&#039;), which appends the argument on the right side to
    the argument on the left side. Please read <a href="language.operators.assignment.php" class="link">Assignment
    Operators</a> for more information.
   </p>

   <p class="para">
    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Hello&nbsp;"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"World!"</span><span style="color: #007700">;&nbsp;</span><span style="color: #FF8000">//&nbsp;now&nbsp;$b&nbsp;contains&nbsp;"Hello&nbsp;World!"<br /><br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"Hello&nbsp;"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #DD0000">"World!"</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;now&nbsp;$a&nbsp;contains&nbsp;"Hello&nbsp;World!"<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
   </p>
   <p class="para">
    See also the manual sections on the
    <a href="language.types.string.php" class="link">String type</a> and
    <a href="ref.strings.php" class="link">String functions</a>.
   </p>
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.operators.array.php">Array Operators<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.operators.logical.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Logical Operators</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.operators.string.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.operators.string&amp;redirect=@w{GYKCBYFH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.string&amp;redirect=@w{GYKCBYFH}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>String Operators</strong>
 </div><div id="allnotes">
 <a name="108595"></a>
 <div class="note">
  <strong class='user'>ghazanfar dot mir at gmail dot com</strong>
  <a href="#108595" class="date">09-May-2012 11:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also use variable variable in concatenation:<br />
<br />
Example:<br />
<br />
$a = "Hello";<br />
$b = "World";<br />
$World = "Variable variable";<br />
<br />
echo $a.$$b; // output: HelloVariable variable</span>
</code></div>
  </div>
 </div>
 <a name="108177"></a>
 <div class="note">
  <strong class='user'>krzysiek</strong>
  <a href="#108177" class="date">04-Apr-2012 09:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
echo 1 ..2; will output 10.2<br />
<br />
this is because "1" is contatenated with "0.2"</span>
</code></div>
  </div>
 </div>
 <a name="88827"></a>
 <div class="note">
  <strong class='user'>hexidecimalgadget at hotmail dot com</strong>
  <a href="#88827" class="date">09-Feb-2009 09:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you attempt to add numbers with a concatenation operator, your result will be the result of those numbers as strings.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">echo </span><span class="string">"thr"</span><span class="keyword">.</span><span class="string">"ee"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//prints the string "three"<br />
</span><span class="keyword">echo </span><span class="string">"twe" </span><span class="keyword">. </span><span class="string">"lve"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//prints the string "twelve"<br />
</span><span class="keyword">echo </span><span class="default">1 </span><span class="keyword">. </span><span class="default">2</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//prints the string "12"<br />
</span><span class="keyword">echo </span><span class="default">1.2</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//prints the number 1.2<br />
</span><span class="keyword">echo </span><span class="default">1</span><span class="keyword">+</span><span class="default">2</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">//prints the number 3<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85358"></a>
 <div class="note">
  <strong class='user'>mariusads::at::helpedia.com</strong>
  <a href="#85358" class="date">27-Aug-2008 02:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Be careful so that you don't type "." instead of ";" at the end of a line.<br />
<br />
It took me more than 30 minutes to debug a long script because of something like this:<br />
<br />
&lt;?<br />
echo 'a'.<br />
$c = 'x';<br />
echo 'b';<br />
echo 'c';<br />
?&gt;<br />
<br />
The output is "axbc", because of the dot on the first line.</span>
</code></div>
  </div>
 </div>
 <a name="83299"></a>
 <div class="note">
  <strong class='user'>mehea</strong>
  <a href="#83299" class="date">19-May-2008 05:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I thought string operators were for use with strings or strings and numbers.&nbsp; But that is incorrect.&nbsp; You can use the '.' operator to concatenate two numbers, as follows:<br />
<br />
echo 1 . 2;<br />
<br />
I assume that behind the scenes the 1 and 2 are converted to strings to allow the concatenation.&nbsp; What triggers the conversion? I'll guess the dot operator.</span>
</code></div>
  </div>
 </div>
 <a name="71062"></a>
 <div class="note">
  <strong class='user'>kevin at metalaxe dot com</strong>
  <a href="#71062" class="date">09-Nov-2006 06:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I ran the follow script and found that using "$var" was 'mostly' slower than using ' '.$var<br />
<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
for( </span><span class="default">$x</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$x </span><span class="keyword">&lt; </span><span class="default">101</span><span class="keyword">; </span><span class="default">$x</span><span class="keyword">++ )<br />
{<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'&lt;br /&gt;&lt;br /&gt;var = int( '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' )&lt;br /&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$timer</span><span class="keyword">-&gt;</span><span class="default">reset</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">100001</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$string </span><span class="keyword">= </span><span class="string">" {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var} {$var}"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset( </span><span class="default">$string </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'One string with 15 $vars was set using one concat 100000 times and took '</span><span class="keyword">.</span><span class="default">$timer</span><span class="keyword">-&gt;</span><span class="default">fetch_time</span><span class="keyword">().</span><span class="string">' seconds to execute &lt;br /&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$timer</span><span class="keyword">-&gt;</span><span class="default">reset</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; for( </span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">100001</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$string </span><span class="keyword">= </span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">.</span><span class="string">' '</span><span class="keyword">.</span><span class="default">$var</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; unset( </span><span class="default">$string </span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">'One string with 15 instances of $var was set using multiple concats 100000 times and took '</span><span class="keyword">.</span><span class="default">$timer</span><span class="keyword">-&gt;</span><span class="default">fetch_time</span><span class="keyword">().</span><span class="string">' seconds to execute'</span><span class="keyword">;<br />
}<br />
exit();<br />
</span><span class="default">?&gt;<br />
</span><br />
Replacing $timer with a generic timing class of course.</span>
</code></div>
  </div>
 </div>
 <a name="63741"></a>
 <div class="note">
  <strong class='user'>caliban at darklock dot com</strong>
  <a href="#63741" class="date">29-Mar-2006 11:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WRT Stephen's note:<br />
<br />
My example of concatenation and array methods of string building does not include the interstitial logic, which is expected to include conditionals. <br />
<br />
Concatenation method:<br />
<br />
$str="This is my list";<br />
if($list=="o") $str.="&lt;ol&gt;";<br />
else $str.="&lt;ul&gt;";<br />
foreach($item as $i) $str.="&lt;li&gt;$i&lt;/li&gt;";<br />
if($list=="o") $str.="&lt;/ol&gt;";<br />
else $str.="&lt;/ul&gt;";<br />
<br />
Array method: <br />
<br />
$str=array("This is my list");<br />
if($list=="o") $str[]="&lt;ol&gt;";<br />
else $str[]="&lt;ul&gt;";<br />
foreach($item as $i) $str[]="&lt;li&gt;$i&lt;/li&gt;";<br />
if($list=="o") $str[]="&lt;/ol&gt;";<br />
else $str[]="&lt;/ul&gt;";<br />
$str=implode("",$str);<br />
<br />
You can't do either of these with a single double-quoted string. However, if what you are doing CAN be done in a single double-quoted string, Stephen is completely correct in observing that you should do that instead of concatenating.</span>
</code></div>
  </div>
 </div>
 <a name="60035"></a>
 <div class="note">
  <strong class='user'>Stephen Clay</strong>
  <a href="#60035" class="date">23-Dec-2005 07:10</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php <br />
</span><span class="string">"{$str1}{$str2}{$str3}"</span><span class="keyword">; </span><span class="comment">// one concat = fast<br />
&nbsp; </span><span class="default">$str1</span><span class="keyword">. </span><span class="default">$str2</span><span class="keyword">. </span><span class="default">$str3</span><span class="keyword">;&nbsp;&nbsp; </span><span class="comment">// two concats = slow<br />
</span><span class="default">?&gt;<br />
</span>Use double quotes to concat more than two strings instead of multiple '.' operators.&nbsp; PHP is forced to re-concatenate with every '.' operator.</span>
</code></div>
  </div>
 </div>
 <a name="48215"></a>
 <div class="note">
  <strong class='user'>caliban at darklock dot com</strong>
  <a href="#48215" class="date">15-Dec-2004 07:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
String concatenation is faster than the array method:<br />
<br />
$str="";<br />
$str.="Some string";<br />
$str.="Some other string";<br />
...<br />
$str.="The last string";<br />
<br />
That runs roughly twice as fast as:<br />
<br />
$str=array();<br />
$str[]="Some string";<br />
$str[]="Some other string";<br />
...<br />
$str[]="The last string";<br />
$str=implode("",$str);<br />
<br />
Not that I think this is a terribly widespread practice, but I've got an awful lot of legacy code with this array method in it and a comment to the effect that it's faster than string concatenation. Testing has shown the exact opposite, so I figured I'd enlighten anyone else with this misconception.</span>
</code></div>
  </div>
 </div>
 <a name="41950"></a>
 <div class="note">
  <strong class='user'>anders dot benke at telia dot com</strong>
  <a href="#41950" class="date">27-Apr-2004 09:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A word of caution - the dot operator has the same precedence as + and -, which can yield unexpected results. <br />
<br />
Example:<br />
<br />
&lt;php<br />
$var = 3;<br />
<br />
echo "Result: " . $var + 3;<br />
?&gt;<br />
<br />
The above will print out "3" instead of "Result: 6", since first the string "Result3" is created and this is then added to 3 yielding 3, non-empty non-numeric strings being converted to 0.<br />
<br />
To print "Result: 6", use parantheses to alter precedence:<br />
<br />
&lt;php<br />
$var = 3;<br />
<br />
echo "Result: " . ($var + 3); <br />
?&gt;</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.operators.string&amp;redirect=@w{GYKCBYFH}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.operators.string&amp;redirect=@w{GYKCBYFH}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.operators.string.php">show source</a> |
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