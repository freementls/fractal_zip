<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: return - Manual</title>
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
 <link rel="prev" href="control-structures.declare.php" />
 <link rel="next" href="function.require.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/return" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/function.return.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/function.return.php" />
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
 <li class="active"><a href="function.return.php">return</a></li>
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
  <a href="function.require.php">require<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.declare.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />declare</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.return.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/function.return.php">Brazilian Portuguese</option>
    <option value="zh/function.return.php">Chinese (Simplified)</option>
    <option value="fr/function.return.php">French</option>
    <option value="de/function.return.php">German</option>
    <option value="ja/function.return.php">Japanese</option>
    <option value="pl/function.return.php">Polish</option>
    <option value="ro/function.return.php">Romanian</option>
    <option value="ru/function.return.php">Russian</option>
    <option value="fa/function.return.php">Persian</option>
    <option value="es/function.return.php">Spanish</option>
    <option value="tr/function.return.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="function.return" class="sect1">
 <h2 class="title">return</h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="simpara">
  If called from within a function, the <em>return</em>
  statement immediately ends execution of the current function, and
  returns its argument as the value of the function
  call. <em>return</em> will also end the execution of
  an  <span class="function"><a href="function.eval.php" class="function">eval()</a></span> statement or script file.
 </p>
 <p class="simpara">
  If called from the global scope, then execution of the current
  script file is ended. If the current script file was
   <span class="function"><a href="function.include.php" class="function">include</a></span>d or  <span class="function"><a href="function.require.php" class="function">require</a></span>d,
  then control is passed back to the calling file. Furthermore, if
  the current script file was  <span class="function"><a href="function.include.php" class="function">include</a></span>d, then
  the value given to <em>return</em> will be returned as
  the value of the  <span class="function"><a href="function.include.php" class="function">include</a></span> call. If
  <em>return</em> is called from within the main script
  file, then script execution ends. If the current script file was
  named by the <a href="ini.core.php#ini.auto-prepend-file" class="link">auto_prepend_file</a> or <a href="ini.core.php#ini.auto-append-file" class="link">auto_append_file</a>
  configuration options in <var class="filename">php.ini</var>,
  then that script file&#039;s execution is ended.
 </p>
 <p class="simpara">For more information, see <a href="functions.returning-values.php" class="link">Returning values</a>.
 </p>
 <p class="para">
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    Note that since <em>return</em> is a language
    construct and not a function, the parentheses surrounding its
    arguments are not required. It is common to leave them out, and you
    actually should do so as PHP has less work to do in this case.
   </span>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    If no parameter is supplied, then the parentheses must be omitted
    and <strong><code>NULL</code></strong> will be
    returned. Calling <em>return</em> with parentheses but
    with no arguments will result in a parse error.
   </span>
  </p></blockquote>
  <blockquote class="note"><p><strong class="note">Note</strong>: 
   <span class="simpara">
    You should <em class="emphasis">never</em> use parentheses around your return
    variable when returning by reference, as this will not work. You can
    only return variables by reference, not the result of a statement. If
    you use <em>return ($a);</em> then you&#039;re not returning a
    variable, but the result of the expression <em>($a)</em>
    (which is, of course, the value of <var class="varname"><var class="varname">$a</var></var>).
    </span>
   </p></blockquote>
 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="function.require.php">require<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.declare.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />declare</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/function.return.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=function.return&amp;redirect=http://www.php.net/manual/en/function.return.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.return&amp;redirect=http://www.php.net/manual/en/function.return.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>return</strong>
 </div><div id="allnotes">
 <a name="97674"></a>
 <div class="note">
  <strong class='user'>MrLavender</strong>
  <a href="#97674" class="date">02-May-2010 10:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
@Radu<br />
<br />
<a href="@w{99AQHN5V}" rel="nofollow" target="_blank">@w{99AQHN5V}</a><br />
<br />
"The value of an assignment expression is the value assigned."<br />
<br />
Note "the value assigned", not "the value assigned to".<br />
<br />
The value assigned in the expression $a['e'] = 'sometxt' is 'sometxt', and that's what you're returning in function a().</span>
</code></div>
  </div>
 </div>
 <a name="96904"></a>
 <div class="note">
  <strong class='user'>Radu</strong>
  <a href="#96904" class="date">22-Mar-2010 03:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When returning an array, you should declare the array before the return, else the result is not as you expect;<br />
<br />
&nbsp; Watch this example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">a</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$a</span><span class="keyword">[</span><span class="string">'e'</span><span class="keyword">] = </span><span class="string">'sometxt'</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">b</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$a</span><span class="keyword">[</span><span class="string">'e'</span><span class="keyword">]&nbsp; = </span><span class="string">'sometxt'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$a</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">c</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">a</span><span class="keyword">())){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'a is array'</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'a is NOT an array'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">b</span><span class="keyword">())){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'b is array'</span><span class="keyword">; <br />
&nbsp;&nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">'b is NOT an array'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This will print:<br />
a is NOT an array<br />
b is array</span>
</code></div>
  </div>
 </div>
 <a name="92621"></a>
 <div class="note">
  <strong class='user'>pgl at yoyo dot org</strong>
  <a href="#92621" class="date">31-Jul-2009 08:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
NB: using return to exit a command-line script will not use the return value as the script's return value. To do that, you need to use, eg, exit(1);</span>
</code></div>
  </div>
 </div>
 <a name="92611"></a>
 <div class="note">
  <strong class='user'>fyrye</strong>
  <a href="#92611" class="date">31-Jul-2009 03:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A side note when you return a conditional value the variable type will inherit its type of Boolean <br />
For example<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$SQL</span><span class="keyword">){<br />
&nbsp;&nbsp; </span><span class="default">$conTemp </span><span class="keyword">= new </span><span class="default">mysqli</span><span class="keyword">(</span><span class="string">"locahost"</span><span class="keyword">, </span><span class="string">"root"</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">);<br />
&nbsp;&nbsp; </span><span class="default">$conTemp</span><span class="keyword">-&gt;</span><span class="default">select_db</span><span class="keyword">(</span><span class="string">"MyDB"</span><span class="keyword">);<br />
&nbsp;&nbsp; return </span><span class="default">$conTemp</span><span class="keyword">-&gt;</span><span class="default">query</span><span class="keyword">(</span><span class="default">$SQL</span><span class="keyword">) or die(</span><span class="string">"Query Failed!"</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$result </span><span class="keyword">= </span><span class="default">foo</span><span class="keyword">(</span><span class="string">"SELECT UserName FROM Users LIMIT 1"</span><span class="keyword">);<br />
echo </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">); </span><span class="comment">//returns Boolean instead of object or die<br />
</span><span class="default">?&gt;<br />
</span><br />
Instead be explicit with your function like so<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">(</span><span class="default">$SQL</span><span class="keyword">){<br />
&nbsp;&nbsp; </span><span class="default">$conTemp </span><span class="keyword">= new </span><span class="default">mysqli</span><span class="keyword">(</span><span class="string">"locahost"</span><span class="keyword">, </span><span class="string">"root"</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">, </span><span class="string">""</span><span class="keyword">);<br />
&nbsp;&nbsp; </span><span class="default">$conTemp</span><span class="keyword">-&gt;</span><span class="default">select_db</span><span class="keyword">(</span><span class="string">"MyDB"</span><span class="keyword">);<br />
&nbsp;&nbsp; if(!</span><span class="default">$result </span><span class="keyword">= </span><span class="default">$conTemp</span><span class="keyword">-&gt;</span><span class="default">query</span><span class="keyword">(</span><span class="default">$SQL</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; return die(</span><span class="string">"Query Failed"</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$result </span><span class="keyword">= </span><span class="default">foo</span><span class="keyword">(</span><span class="string">"SELECT UserName FROM Users LIMIT 1"</span><span class="keyword">);<br />
echo </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">); </span><span class="comment">//Now will return Object or die<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="86193"></a>
 <div class="note">
  <strong class='user'>list at regularoddity dot com</strong>
  <a href="#86193" class="date">07-Oct-2008 09:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As obvious as it may seem, it might still be useful to point out that return called without any value returns null.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp; return;<br />
}<br />
print </span><span class="default">gettype</span><span class="keyword">(</span><span class="default">test</span><span class="keyword">()) . </span><span class="string">"\n"</span><span class="keyword">;<br />
print (</span><span class="default">test</span><span class="keyword">()?</span><span class="string">'true'</span><span class="keyword">:</span><span class="string">'false'</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
print (!</span><span class="default">test</span><span class="keyword">()?</span><span class="string">'true'</span><span class="keyword">:</span><span class="string">'false'</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
print (</span><span class="default">test</span><span class="keyword">() === </span><span class="default">false</span><span class="keyword">?</span><span class="string">'true'</span><span class="keyword">:</span><span class="string">'false'</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This returns:<br />
<br />
NULL<br />
false<br />
true<br />
false</span>
</code></div>
  </div>
 </div>
 <a name="85112"></a>
 <div class="note">
  <strong class='user'>andrew at neonsurge dot com</strong>
  <a href="#85112" class="date">15-Aug-2008 01:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Response to stoic's message below...<br />
<br />
I believe the way you've explained this for people may be a bit confusing, and your verbiage is incorrect.&nbsp; Your script below is technically calling return from a global scope, but as it says right after that in the description above... "If the current script file was include()ed or require()ed, then control is passed back to the calling file".&nbsp; You are in a included file.&nbsp; Just making sure that is clear.<br />
<br />
Now, the way php works is before it executes actual code it does what you call "processing" is really just a syntax check.&nbsp; It does this every time per-file that is included before executing that file.&nbsp; This is a GOOD feature, as it makes sure not to run any part of non-functional code.&nbsp; What your example might have also said... is that in doing this syntax check it does not execute code, merely runs through your file (or include) checking for syntax errors before execution.&nbsp; To show that, you should put the echo "b"; and echo "a"; at the start of each file.&nbsp; This will show that "b" is echoed once, and then "a" is echoed only once, because the first time it syntax checked a.php, it was ok.&nbsp; But the second time the syntax check failed and thus it was not executed again and terminated execution of the application due to a syntax error.<br />
<br />
Just something to help clarify what you have stated in your comments.</span>
</code></div>
  </div>
 </div>
 <a name="83663"></a>
 <div class="note">
  <strong class='user'>stoic</strong>
  <a href="#83663" class="date">06-Jun-2008 06:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just to clear things up, if using return on a global scope it will end EXECUTION but NOT PROCESSING.<br />
<br />
for example:<br />
<br />
file a.php<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if(</span><span class="default">defined</span><span class="keyword">(</span><span class="string">"A"</span><span class="keyword">)) return;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"A"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
<br />
echo </span><span class="string">"Hello"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
file b.php<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">include(</span><span class="string">"a.php"</span><span class="keyword">);<br />
include(</span><span class="string">"a.php"</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
will output "Hello" only once.<br />
<br />
but if file a.php is<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">if(</span><span class="default">defined</span><span class="keyword">(</span><span class="string">"A"</span><span class="keyword">)) return;<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"A"</span><span class="keyword">, </span><span class="default">true</span><span class="keyword">);<br />
<br />
function </span><span class="default">foo</span><span class="keyword">(){<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
running file b.php will produce error:<br />
<br />
Fatal Error: Cannot redeclare foo()...</span>
</code></div>
  </div>
 </div>
 <a name="79548"></a>
 <div class="note">
  <strong class='user'>Denis.Gorbachev</strong>
  <a href="#79548" class="date">02-Dec-2007 02:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
direct true&nbsp; &nbsp; 0.59850406646729<br />
direct false&nbsp; &nbsp; 0.62642693519592<br />
indirect true&nbsp; &nbsp; 0.75077891349792<br />
indirect false&nbsp; &nbsp; 0.73496103286743<br />
<br />
It is generally more true, because indirect method implies creating additional variable and assigning a value to it.<br />
<br />
But, you know, "results may vary".</span>
</code></div>
  </div>
 </div>
 <a name="78442"></a>
 <div class="note">
  <strong class='user'>mr dot xanadu at gmail dot com</strong>
  <a href="#78442" class="date">12-Oct-2007 01:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I was wondering what was quicker:<br />
- return a boolean as soon I know it's value ('direct') or<br />
- save the boolean in a variable and return it at the function's end.<br />
<br />
<span class="default">&lt;?php<br />
$times </span><span class="keyword">= </span><span class="default">50000</span><span class="keyword">;<br />
<br />
function </span><span class="default">return_direct </span><span class="keyword">(</span><span class="default">$boolean</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$boolean </span><span class="keyword">== </span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
}<br />
<br />
function </span><span class="default">return_indirect </span><span class="keyword">(</span><span class="default">$boolean</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$boolean </span><span class="keyword">== </span><span class="default">true</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$return</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="comment">/* Direct, return true */<br />
<br />
</span><span class="default">$time_start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$times</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">return_direct</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$time_end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$time_direct_true </span><span class="keyword">= </span><span class="default">$time_end </span><span class="keyword">- </span><span class="default">$time_start</span><span class="keyword">;<br />
<br />
</span><span class="comment">/* Direct, return false */<br />
<br />
</span><span class="default">$time_start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$times</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">return_direct</span><span class="keyword">(</span><span class="default">false</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$time_end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$time_direct_false </span><span class="keyword">= </span><span class="default">$time_end </span><span class="keyword">- </span><span class="default">$time_start</span><span class="keyword">;<br />
<br />
</span><span class="comment">/* Indirect, return true */<br />
<br />
</span><span class="default">$time_start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$times</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">return_indirect</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$time_end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$time_indirect_true </span><span class="keyword">= </span><span class="default">$time_end </span><span class="keyword">- </span><span class="default">$time_start</span><span class="keyword">;<br />
<br />
</span><span class="comment">/* Direct, return false */<br />
<br />
</span><span class="default">$time_start </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
<br />
for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$times</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">return_indirect</span><span class="keyword">(</span><span class="default">false</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">$time_end </span><span class="keyword">= </span><span class="default">microtime</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
</span><span class="default">$time_indirect_false </span><span class="keyword">= </span><span class="default">$time_end </span><span class="keyword">- </span><span class="default">$time_start</span><span class="keyword">;<br />
<br />
echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"direct true\t" </span><span class="keyword">. </span><span class="default">$time_direct_true</span><span class="keyword">;<br />
echo </span><span class="string">"\ndirect false\t" </span><span class="keyword">. </span><span class="default">$time_direct_false</span><span class="keyword">;<br />
echo </span><span class="string">"\nindirect true\t" </span><span class="keyword">. </span><span class="default">$time_indirect_true</span><span class="keyword">;<br />
echo </span><span class="string">"\nindirect false\t" </span><span class="keyword">. </span><span class="default">$time_indirect_false</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;pre&gt;"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Representative results:<br />
direct true&nbsp; &nbsp; 0.163973093033<br />
direct false&nbsp; &nbsp; 0.1270840168<br />
indirect true&nbsp; &nbsp; 0.0733940601349<br />
indirect false&nbsp; &nbsp; 0.0742440223694<br />
<br />
Conclusion: saving the result in a variable appears to be faster. (Please note that my test functions are very simple, maybe it's slower on longer functions)</span>
</code></div>
  </div>
 </div>
 <a name="76655"></a>
 <div class="note">
  <strong class='user'>Spacecat</strong>
  <a href="#76655" class="date">24-Jul-2007 06:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
regardez this code:<br />
<br />
print pewt( "hello!" );<br />
<br />
function pewt( $arg )<br />
{<br />
<br />
include( "some_code.inc" );<br />
<br />
}<br />
<br />
some_code.inc:<br />
<br />
&nbsp; return strtoupper( $arg );<br />
<br />
.. after much hair pulling, discovered why nothing was being returned by the "some_code.inc" code in the function .. the return simply returns the result TO the function (giving the include function a value), not to the CALLING (print pewt). This works:<br />
<br />
print pewt( "hello!" );<br />
<br />
function pewt( $arg )<br />
{<br />
<br />
return include( "some_code.inc" );<br />
<br />
}<br />
<br />
So, RETURN works relative to block it is executed within.</span>
</code></div>
  </div>
 </div>
 <a name="59866"></a>
 <div class="note">
  <strong class='user'>warhog at warhog dot net</strong>
  <a href="#59866" class="date">18-Dec-2005 12:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
for those of you who think that using return in a script is the same as using exit note that: using return just exits the execution of the current script, exit the whole execution.<br />
<br />
look at that example:<br />
<br />
a.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">"b.php"</span><span class="keyword">);<br />
echo </span><span class="string">"a"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
b.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="string">"b"</span><span class="keyword">;<br />
return;<br />
</span><span class="default">?&gt;<br />
</span><br />
(executing a.php:) will echo "ba".<br />
<br />
whereas (b.php modified):<br />
<br />
a.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">include(</span><span class="string">"b.php"</span><span class="keyword">);<br />
echo </span><span class="string">"a"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
b.php<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">echo </span><span class="string">"b"</span><span class="keyword">;<br />
exit;<br />
</span><span class="default">?&gt;<br />
</span><br />
(executing a.php:) will echo "b".</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=function.return&amp;redirect=http://www.php.net/manual/en/function.return.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.return&amp;redirect=http://www.php.net/manual/en/function.return.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/function.return.php">show source</a> |
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