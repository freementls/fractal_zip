<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Variable variables - Manual</title>
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
 <link rel="index" href="language.variables.php" />
 <link rel="prev" href="language.variables.scope.php" />
 <link rel="next" href="language.variables.external.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/variables.variable" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.variables.variable.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{QGD8CT77}" />
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
 <li class="header up"><a href="language.variables.php">Variables</a></li>
 <li><a href="language.variables.basics.php">Basics</a></li>
 <li><a href="language.variables.predefined.php">Predefined Variables</a></li>
 <li><a href="language.variables.scope.php">Variable scope</a></li>
 <li class="active"><a href="language.variables.variable.php">Variable variables</a></li>
 <li><a href="language.variables.external.php">Variables From External Sources</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.variables.external.php">Variables From External Sources<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.scope.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variable scope</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.variable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.variables.variable.php">Brazilian Portuguese</option>
    <option value="zh/language.variables.variable.php">Chinese (Simplified)</option>
    <option value="fr/language.variables.variable.php">French</option>
    <option value="de/language.variables.variable.php">German</option>
    <option value="ja/language.variables.variable.php">Japanese</option>
    <option value="pl/language.variables.variable.php">Polish</option>
    <option value="ro/language.variables.variable.php">Romanian</option>
    <option value="ru/language.variables.variable.php">Russian</option>
    <option value="fa/language.variables.variable.php">Persian</option>
    <option value="es/language.variables.variable.php">Spanish</option>
    <option value="tr/language.variables.variable.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.variables.variable" class="sect1">
   <h2 class="title">Variable variables</h2>

   <p class="simpara">
    Sometimes it is convenient to be able to have variable variable
    names.  That is, a variable name which can be set and used
    dynamically.  A normal variable is set with a statement such as:
   </p>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'hello'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="simpara">
    A variable variable takes the value of a variable and treats that
    as the name of a variable.  In the above example,
    <em class="emphasis">hello</em>, can be used as the name of a variable
    by using two dollar signs. i.e.
   </p>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">$</span><span style="color: #0000BB">$a&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'world'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="simpara">
    At this point two variables have been defined and stored in the
    PHP symbol tree: <var class="varname"><var class="varname">$a</var></var> with contents &quot;hello&quot; and
    <var class="varname"><var class="varname">$hello</var></var> with contents &quot;world&quot;.  Therefore, this
    statement:
   </p>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">&nbsp;</span><span style="color: #007700">${</span><span style="color: #0000BB">$a</span><span style="color: #007700">}</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="simpara">
    produces the exact same output as:
   </p>

   <div class="informalexample">
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$a</span><span style="color: #DD0000">&nbsp;</span><span style="color: #0000BB">$hello</span><span style="color: #DD0000">"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="simpara">
    i.e. they both produce: <span class="computeroutput">hello world</span>.
   </p>

   <p class="simpara">
    In order to use variable variables with arrays, you have to
    resolve an ambiguity problem.  That is, if you write
    <var class="varname"><var class="varname">$$a[1]</var></var> then the parser needs to know if you
    meant to use <var class="varname"><var class="varname">$a[1]</var></var> as a variable, or if you
    wanted <var class="varname"><var class="varname">$$a</var></var> as the variable and then the [1]
    index from that variable.  The syntax for resolving this ambiguity
    is: <var class="varname"><var class="varname">${$a[1]}</var></var> for the first case and
    <var class="varname"><var class="varname">${$a}[1]</var></var> for the second. 
   </p>

   <p class="simpara">
    Class properties may also be accessed using variable property
    names. The variable property name will be resolved within the
    scope from which the call is made. For instance, if you have an
    expression such as <var class="varname"><var class="varname">$foo->$bar</var></var>, then the local
    scope will be examined for <var class="varname"><var class="varname">$bar</var></var> and its value
    will be used as the name of the property
    of <var class="varname"><var class="varname">$foo</var></var>. This is also true
    if <var class="varname"><var class="varname">$bar</var></var> is an array access.
   </p>

   <p class="para">
    <div class="example" id="example-105">
     <p><strong>Example #1 Variable property example</strong></p>
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">foo&nbsp;</span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;var&nbsp;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'I&nbsp;am&nbsp;bar.'</span><span style="color: #007700">;<br />}<br /><br /></span><span style="color: #0000BB">$foo&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">foo</span><span style="color: #007700">();<br /></span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">$baz&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'baz'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'quux'</span><span style="color: #007700">);<br />echo&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$bar&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />echo&nbsp;</span><span style="color: #0000BB">$foo</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">$baz</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     <div class="example-contents"><p>The above example will output:</p></div>
     <div class="example-contents screen"><br />
I am bar.<br />
I am bar.<br />
     </div>
    </div>
   </p>

   <div class="warning"><strong class="warning">Warning</strong>
    <p class="simpara">
     Please note that variable variables cannot be used with PHP&#039;s 
     <a href="language.variables.superglobals.php" class="link">Superglobal arrays</a>
     within functions or class methods. The variable <em>$this</em>
     is also a special variable that cannot be referenced dynamically.
    </p>
   </div>
  
  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.variables.external.php">Variables From External Sources<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.scope.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variable scope</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.variable.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.variables.variable&amp;redirect=@w{QGD8CT77}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.variable&amp;redirect=@w{QGD8CT77}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Variable variables</strong>
 </div><div id="allnotes">
 <a name="109230"></a>
 <div class="note">
  <strong class='user'>nils dot rocine at gmail dot com</strong>
  <a href="#109230" class="date">28-Jun-2012 01:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Variable Class Instantiation with Namespace Gotcha:<br />
<br />
Say you have a class you'd like to instantiate via a variable (with a string value of the Class name)<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Foo <br />
</span><span class="keyword">{ <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() <br />
&nbsp;&nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"I'm a real class!" </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= </span><span class="string">'Foo'</span><span class="keyword">;<br />
<br />
</span><span class="default">$instance </span><span class="keyword">= new </span><span class="default">$class</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
The above works fine UNLESS you are in a (defined) namespace. Then you must provide the full namespaced identifier of the class as shown below. This is the case EVEN THOUGH the instancing happens in the same namespace. Instancing a class normally (not through a variable) does not require the namespace. This seems to establish the pattern that if you are using an namespace and you have a class name in a string, you must provide the namespace with the class for the PHP engine to correctly resolve (other cases: class_exists(), interface_exists(), etc.) <br />
<br />
<span class="default">&lt;?php<br />
<br />
namespace MyNamespace</span><span class="keyword">;<br />
<br />
class </span><span class="default">Foo <br />
</span><span class="keyword">{ <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">() <br />
&nbsp;&nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"I'm a real class!" </span><span class="keyword">. </span><span class="default">PHP_EOL</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$class </span><span class="keyword">= </span><span class="string">'MyNamespace\Foo'</span><span class="keyword">;<br />
<br />
</span><span class="default">$instance </span><span class="keyword">= new </span><span class="default">$class</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="108141"></a>
 <div class="note">
  <strong class='user'>ckelley at ca-cycleworks dot com</strong>
  <a href="#108141" class="date">01-Apr-2012 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unlike as stated near the bottom of this thread, using variables to point to an object does not always work. In my case, that is the object returned from `new SimpleXMLElement($xmlstr)`.<br />
<br />
Once my nodes got 3 and 4 deep to access customer information from orders, I knew something drastic had to be done.<br />
<br />
<span class="default">&lt;?php<br />
$xmlstr </span><span class="keyword">=</span><span class="string">"&lt;xml&gt;&lt;foo&gt;&lt;bar&gt;&lt;you&gt;Blah&lt;/you&gt;&lt;/bar&gt;&lt;/foo&gt;&lt;/xml&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">$xml</span><span class="keyword">=</span><span class="default">SimpleXMLElement</span><span class="keyword">(</span><span class="default">$xmlstr</span><span class="keyword">);<br />
</span><span class="comment">// no matter what you feed it, it works...<br />
</span><span class="default">$lvl1</span><span class="keyword">=</span><span class="string">"\$xml-&gt;foo"</span><span class="keyword">;<br />
echo </span><span class="default">n</span><span class="keyword">(</span><span class="default">$lvl1</span><span class="keyword">.</span><span class="string">"-&gt;bar-&gt;you"</span><span class="keyword">);<br />
<br />
</span><span class="comment">// n will return Blah as we would hope.<br />
<br />
</span><span class="keyword">function </span><span class="default">n</span><span class="keyword">(</span><span class="default">$node</span><span class="keyword">){ <br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$xml</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return eval(</span><span class="string">"return $node;"</span><span class="keyword">);&nbsp; <br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105293"></a>
 <div class="note">
  <strong class='user'>Omar Juvera</strong>
  <a href="#105293" class="date">07-Aug-2011 09:13</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In this example, I have the variable $city.<br />
To store the variable $city inside another variable:<br />
<br />
<span class="default">&lt;?php<br />
$city </span><span class="keyword">= </span><span class="string">'New York'</span><span class="keyword">;<br />
<br />
</span><span class="default">$var_container </span><span class="keyword">= </span><span class="string">'city'</span><span class="keyword">; </span><span class="comment">//$var_container will store the variable $city <br />
<br />
</span><span class="keyword">echo </span><span class="string">"CONTAINER's var: " </span><span class="keyword">. </span><span class="default">$var_container</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"CONTAINER's value: " </span><span class="keyword">. $</span><span class="default">$var_container</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"VAR city: " </span><span class="keyword">. </span><span class="default">$city</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The OUTPUT is:<br />
CONTAINER's var: city<br />
CONTAINER's value: New York<br />
VAR city: New York</span>
</code></div>
  </div>
 </div>
 <a name="105282"></a>
 <div class="note">
  <strong class='user'>Omar Juvera</strong>
  <a href="#105282" class="date">07-Aug-2011 01:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The example given in the php manual is confusing!<br />
I think this example it's easier to understand: <br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">//Let's create a new variable: $new_variable_1<br />
</span><span class="default">$var_name </span><span class="keyword">= </span><span class="string">"new_variable_1"</span><span class="keyword">; </span><span class="comment">//$var_name will store the NAME of the new variable<br />
<br />
//Let's assign a value to that [$new_variable_1] variable:<br />
</span><span class="keyword">$</span><span class="default">$var_name&nbsp; </span><span class="keyword">= </span><span class="string">"value 1"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_1 = "value 1"<br />
<br />
</span><span class="keyword">echo </span><span class="string">"VARIABLE: " </span><span class="keyword">. </span><span class="default">$var_name</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"VALUE: " </span><span class="keyword">. $</span><span class="default">$var_name</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The OUTPUT is:<br />
VARIABLE: new_variable_1<br />
VALUE: value 1 <br />
<br />
You can also create new variables in a loop:<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">for( </span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">6</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++ )<br />
{<br />
</span><span class="default">$var_name</span><span class="keyword">[] = </span><span class="string">"new_variable_" </span><span class="keyword">. </span><span class="default">$i</span><span class="keyword">; </span><span class="comment">//$var_name[] will hold the new variable NAME<br />
</span><span class="keyword">}<br />
<br />
${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]}&nbsp; = </span><span class="string">"value 1"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_1 = "value 1"<br />
</span><span class="keyword">${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]}&nbsp; = </span><span class="string">"value 2"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_2 = "value 2"<br />
</span><span class="keyword">${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">2</span><span class="keyword">]}&nbsp; = </span><span class="string">"value 3"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_3 = "value 3"<br />
</span><span class="keyword">${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">3</span><span class="keyword">]}&nbsp; = </span><span class="string">"value 4"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_4 = "value 4"<br />
</span><span class="keyword">${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">4</span><span class="keyword">]}&nbsp; = </span><span class="string">"value 5"</span><span class="keyword">; </span><span class="comment">//Value of $new_variable_5 = "value 5"<br />
<br />
</span><span class="keyword">echo </span><span class="string">"VARIABLE: " </span><span class="keyword">. </span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] . </span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
echo </span><span class="string">"VALUE: " </span><span class="keyword">. ${</span><span class="default">$var_name</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]};<br />
</span><span class="default">?&gt;<br />
</span><br />
The OUTPUT is:<br />
VARIABLE: new_variable_1<br />
VALUE: value 1</span>
</code></div>
  </div>
 </div>
 <a name="101977"></a>
 <div class="note">
  <strong class='user'>andre at AWizardOfAss dot com</strong>
  <a href="#101977" class="date">21-Jan-2011 01:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To generate a family of variables, such as $a1, $a2, $a3, etc., one can use "variable variables" as follows:<br />
<br />
<span class="default">&lt;?php <br />
</span><span class="keyword">for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">5</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp; ${</span><span class="default">a</span><span class="keyword">.</span><span class="default">$i</span><span class="keyword">} = </span><span class="string">"value"</span><span class="keyword">;<br />
}&nbsp; &nbsp; <br />
<br />
echo </span><span class="string">"$a1, $a2, $a3, $a4, $a5"</span><span class="keyword">;<br />
</span><span class="comment">//Output is value, value, value, value, value<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that the correct syntax is ${a.$i} rather than the perhaps more intuitive $a{$i}<br />
<br />
The dot (.) is the string concatenation operator.<br />
<br />
A family of variables might be used as an alternative to arrays.</span>
</code></div>
  </div>
 </div>
 <a name="99516"></a>
 <div class="note">
  <strong class='user'>Sam Yong - hellclanner at live dot com</strong>
  <a href="#99516" class="date">21-Aug-2010 02:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Want to access object's property or array value using variable variables but not possible?<br />
<br />
Here's a workaround to it:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">varvar</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">,</span><span class="string">'-&gt;'</span><span class="keyword">) !== </span><span class="default">false</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// Accessing object property<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$parts </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">'-&gt;'</span><span class="keyword">,</span><span class="default">$str</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global ${</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]};<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return ${</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]}-&gt;</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; }elseif(</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">,</span><span class="string">'['</span><span class="keyword">) !== </span><span class="default">false </span><span class="keyword">&amp;&amp; </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$str</span><span class="keyword">,</span><span class="string">']'</span><span class="keyword">) !== </span><span class="default">false</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$parts </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">'['</span><span class="keyword">,</span><span class="default">$str</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global ${</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]};<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$parts</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] = </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">],</span><span class="default">0</span><span class="keyword">,</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">])-</span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return ${</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]}[</span><span class="default">$parts</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]];<br />
&nbsp;&nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global ${</span><span class="default">$str</span><span class="keyword">};<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return ${</span><span class="default">$str</span><span class="keyword">};<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$arrayTest </span><span class="keyword">= array(</span><span class="string">'value0'</span><span class="keyword">, </span><span class="string">'value1'</span><span class="keyword">, </span><span class="string">'test1'</span><span class="keyword">=&gt; </span><span class="string">'value2'</span><span class="keyword">, </span><span class="string">'test2'</span><span class="keyword">=&gt; </span><span class="string">'value3'</span><span class="keyword">);<br />
</span><span class="default">$objectTest </span><span class="keyword">= (object)</span><span class="default">$arrayTest</span><span class="keyword">;<br />
<br />
</span><span class="default">$test </span><span class="keyword">= </span><span class="string">'arrayTest[1]'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">varvar</span><span class="keyword">(</span><span class="default">$test</span><span class="keyword">)); </span><span class="comment">// string(6) "value1"<br />
</span><span class="default">var_dump</span><span class="keyword">($</span><span class="default">$test</span><span class="keyword">); </span><span class="comment">// NULL<br />
<br />
</span><span class="default">$test2 </span><span class="keyword">= </span><span class="string">'objectTest-&gt;test2'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">varvar</span><span class="keyword">(</span><span class="default">$test2</span><span class="keyword">)); </span><span class="comment">// string(6) "value3"<br />
</span><span class="default">var_dump</span><span class="keyword">($</span><span class="default">$test2</span><span class="keyword">); </span><span class="comment">// NULL<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Cheers<br />
Sam-Mauris Yong</span>
</code></div>
  </div>
 </div>
 <a name="98641"></a>
 <div class="note">
  <strong class='user'>mason</strong>
  <a href="#98641" class="date">28-Jun-2010 12:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
PHP actually supports invoking a new instance of a class using a variable class name since at least version 5.2<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">'Hello world!'</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$my_foo </span><span class="keyword">= </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= new </span><span class="default">$my_foo</span><span class="keyword">();<br />
</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">hello</span><span class="keyword">(); </span><span class="comment">//prints 'Hello world!'<br />
</span><span class="default">?&gt;<br />
</span><br />
Additionally, you can access static methods and properties using variable class names, but only since PHP 5.3<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp;&nbsp; public static function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">'Hello world!'</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">$my_foo </span><span class="keyword">= </span><span class="string">'Foo'</span><span class="keyword">;<br />
</span><span class="default">$my_foo</span><span class="keyword">::</span><span class="default">hello</span><span class="keyword">(); </span><span class="comment">//prints 'Hello world!'<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97222"></a>
 <div class="note">
  <strong class='user'>userb at exampleb dot org</strong>
  <a href="#97222" class="date">08-Apr-2010 02:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="comment">//You can even add more Dollar Signs<br />
<br />
&nbsp; </span><span class="default">$Bar </span><span class="keyword">= </span><span class="string">"a"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$Foo </span><span class="keyword">= </span><span class="string">"Bar"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$World </span><span class="keyword">= </span><span class="string">"Foo"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$Hello </span><span class="keyword">= </span><span class="string">"World"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$a </span><span class="keyword">= </span><span class="string">"Hello"</span><span class="keyword">;<br />
<br />
&nbsp; </span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns Hello<br />
&nbsp; </span><span class="keyword">$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns World<br />
&nbsp; </span><span class="keyword">$$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns Foo<br />
&nbsp; </span><span class="keyword">$$$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns Bar<br />
&nbsp; </span><span class="keyword">$$$$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns a<br />
<br />
&nbsp; </span><span class="keyword">$$$$$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns Hello<br />
&nbsp; </span><span class="keyword">$$$$$$</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">//Returns World<br />
<br />
&nbsp; //... and so on ...//<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96225"></a>
 <div class="note">
  <strong class='user'>dlorre at yahoo dot com</strong>
  <a href="#96225" class="date">16-Feb-2010 12:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Adding an element directly to an array using variables:<br />
<br />
<span class="default">&lt;?php<br />
$tab </span><span class="keyword">= array(</span><span class="string">"one"</span><span class="keyword">, </span><span class="string">"two"</span><span class="keyword">, </span><span class="string">"three"</span><span class="keyword">) ;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"tab" </span><span class="keyword">;<br />
$</span><span class="default">$a</span><span class="keyword">[] =</span><span class="string">"four" </span><span class="keyword">; </span><span class="comment">// &lt;==== fatal error<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$tab</span><span class="keyword">) ;<br />
</span><span class="default">?&gt;<br />
</span>will issue this error:<br />
<br />
Fatal error: Cannot use [] for reading<br />
<br />
This is not a bug, you need to use the {} syntax to remove the ambiguity.<br />
<br />
<span class="default">&lt;?php<br />
$tab </span><span class="keyword">= array(</span><span class="string">"one"</span><span class="keyword">, </span><span class="string">"two"</span><span class="keyword">, </span><span class="string">"three"</span><span class="keyword">) ;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"tab" </span><span class="keyword">;<br />
${</span><span class="default">$a</span><span class="keyword">}[] =&nbsp; </span><span class="string">"four" </span><span class="keyword">; </span><span class="comment">// &lt;==== this is the correct way to do it<br />
</span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$tab</span><span class="keyword">) ;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94740"></a>
 <div class="note">
  <strong class='user'>php at willshouse dot the-usual-uk-tld</strong>
  <a href="#94740" class="date">21-Nov-2009 04:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you need to access one of the superglobals using a variable variable, you can look it up in $GLOBALS:<br />
<br />
<span class="default">&lt;?PHP<br />
define</span><span class="keyword">(</span><span class="string">'FORM_METHOD'</span><span class="keyword">, </span><span class="string">'post'</span><span class="keyword">);<br />
<br />
function </span><span class="default">getFormVariable</span><span class="keyword">( </span><span class="default">$fieldName</span><span class="keyword">, </span><span class="default">$defaultValue </span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; global </span><span class="default">$FILTER_METHOD</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$getpost </span><span class="keyword">= </span><span class="default">$GLOBALS</span><span class="keyword">[ </span><span class="string">'_' </span><span class="keyword">. </span><span class="default">strtoupper</span><span class="keyword">(</span><span class="default">FILTER_METHOD</span><span class="keyword">) ];<br />
&nbsp;&nbsp;&nbsp; if ( ! </span><span class="default">array_key_exists</span><span class="keyword">( </span><span class="default">$fieldName</span><span class="keyword">, </span><span class="default">$getpost&nbsp; &nbsp; </span><span class="keyword">) )&nbsp; &nbsp; { return </span><span class="default">$defaultValue</span><span class="keyword">; }<br />
&nbsp;&nbsp;&nbsp; if ( empty(&nbsp; &nbsp; </span><span class="default">$getpost</span><span class="keyword">[ </span><span class="default">$fieldName </span><span class="keyword">]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ) )&nbsp; &nbsp; { return </span><span class="default">$defaultValue</span><span class="keyword">; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$getpost</span><span class="keyword">[ </span><span class="default">$fieldName </span><span class="keyword">];<br />
}<br />
<br />
echo </span><span class="string">"&lt;form method=\""</span><span class="keyword">.</span><span class="default">FORM_METHOD</span><span class="keyword">.</span><span class="string">"\"&gt;\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94634"></a>
 <div class="note">
  <strong class='user'>al at o3strategies dot com</strong>
  <a href="#94634" class="date">15-Nov-2009 06:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is an extremely handy use for variable variables especially when dealing with direct data modeling.&nbsp; This will allow you to automatically set object properties based on a query result. When new fields are added to the table, the class will receive these properties automatically. This is great for maintaining user data or other large tables. Your property names will be bound to your column name in the database, making maintenance worry free. This method uses $this-&gt;{$var} for the variable variable creation.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">testTableData</span><span class="keyword">() {<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">testTableData</span><span class="keyword">(){&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// testTable includes the columns: name, user, date<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$query </span><span class="keyword">= </span><span class="string">"SELECT * FROM testTable"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$result </span><span class="keyword">= </span><span class="default">mysql_query</span><span class="keyword">(</span><span class="default">$query</span><span class="keyword">) or die (</span><span class="default">mysql_error</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$row </span><span class="keyword">= </span><span class="default">mysql_fetch_array</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$row </span><span class="keyword">as </span><span class="default">$var </span><span class="keyword">=&gt; </span><span class="default">$key</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$var</span><span class="keyword">} = </span><span class="default">$key</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="comment">// Access table properties<br />
</span><span class="default">$table </span><span class="keyword">= new </span><span class="default">testTableData</span><span class="keyword">();<br />
<br />
echo </span><span class="default">$table</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;<br />
echo </span><span class="default">$table</span><span class="keyword">-&gt;</span><span class="default">user</span><span class="keyword">;<br />
echo </span><span class="default">$table</span><span class="keyword">-&gt;</span><span class="default">date</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94396"></a>
 <div class="note">
  <strong class='user'>Matthew (mwwaygoo AT hotmail DOT com)</strong>
  <a href="#94396" class="date">02-Nov-2009 08:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A note on Variable variables/functions and classes<br />
<br />
To store a function name in a variable and call it later, within a class, you do the following:-<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">test_class<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$func</span><span class="keyword">=</span><span class="string">'display_UK'</span><span class="keyword">;&nbsp; </span><span class="comment">// function name *<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">display_UK</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Hello"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">display_FR</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Bonjour"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">display</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">func</span><span class="keyword">}(); </span><span class="comment">// NOTE the brackets MUST be here and not in the function name above *<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
}<br />
<br />
</span><span class="default">$test</span><span class="keyword">=new </span><span class="default">test_class</span><span class="keyword">();<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">display_UK</span><span class="keyword">(); </span><span class="comment">// to test they work directly<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">display_FR</span><span class="keyword">();<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">display</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
This allows you to specify the function required. It works better then a big switch statement as it allows for extending the class more easily. (ie adding display_ES(); )</span>
</code></div>
  </div>
 </div>
 <a name="91746"></a>
 <div class="note">
  <strong class='user'>moomin</strong>
  <a href="#91746" class="date">24-Jun-2009 08:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If $something is 'myvar' then you can use $obj-&gt;{"_$something"} to get the value of $obj-&gt;_myvar without having to use eval.</span>
</code></div>
  </div>
 </div>
 <a name="91239"></a>
 <div class="note">
  <strong class='user'>aditeojr at yahoo dot co dot uk</strong>
  <a href="#91239" class="date">01-Jun-2009 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Parsing and retrieving a value from superglobals, by a specified order, looping until it find one :<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">GetInputString</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$default_value </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">, </span><span class="default">$format </span><span class="keyword">= </span><span class="string">"GPCS"</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//order of retrieve default GPCS (get, post, cookie, session);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$format_defines </span><span class="keyword">= array (<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'G'</span><span class="keyword">=&gt;</span><span class="string">'_GET'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'P'</span><span class="keyword">=&gt;</span><span class="string">'_POST'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'C'</span><span class="keyword">=&gt;</span><span class="string">'_COOKIE'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'S'</span><span class="keyword">=&gt;</span><span class="string">'_SESSION'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'R'</span><span class="keyword">=&gt;</span><span class="string">'_REQUEST'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">'F'</span><span class="keyword">=&gt;</span><span class="string">'_FILES'</span><span class="keyword">,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; );<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">preg_match_all</span><span class="keyword">(</span><span class="string">"/[G|P|C|S|R|F]/"</span><span class="keyword">, </span><span class="default">$format</span><span class="keyword">, </span><span class="default">$matches</span><span class="keyword">); </span><span class="comment">//splitting to globals order<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">foreach (</span><span class="default">$matches</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] as </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$glb</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( isset (</span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$format_defines</span><span class="keyword">[</span><span class="default">$glb</span><span class="keyword">]][</span><span class="default">$name</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {&nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="default">$format_defines</span><span class="keyword">[</span><span class="default">$glb</span><span class="keyword">]][</span><span class="default">$name</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$default_value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91181"></a>
 <div class="note">
  <strong class='user'>php at ianco dot co dot uk</strong>
  <a href="#91181" class="date">28-May-2009 02:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="comment">// $variable-name = 'parse error';<br />
// You can't do that but you can do this:<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">'variable-name'</span><span class="keyword">;<br />
$</span><span class="default">$a </span><span class="keyword">= </span><span class="string">'hello'</span><span class="keyword">;<br />
echo </span><span class="default">$variable</span><span class="keyword">-</span><span class="default">name </span><span class="keyword">. </span><span class="string">' ' </span><span class="keyword">. $</span><span class="default">$a</span><span class="keyword">; </span><span class="comment">// Gives&nbsp; &nbsp;&nbsp; 0 hello<br />
</span><span class="default">?&gt;<br />
</span><br />
For a particular reason I had been using some variable names with hyphens for ages. There was no problem because they were only referenced via a variable variable. I only saw a parse error much later, when I tried to reference one directly. It took a while to realise that illegal hyphens were the cause because the parse error only occurs on assignment.</span>
</code></div>
  </div>
 </div>
 <a name="87564"></a>
 <div class="note">
  <strong class='user'>nick at customdesigns dot ca</strong>
  <a href="#87564" class="date">10-Dec-2008 10:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
On the topic of variable variables with arrays, I have a simple function that solves the issue. It works for both indexed and associative arrays, and allows use with superglobals.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">VariableArray</span><span class="keyword">(</span><span class="default">$arr</span><span class="keyword">, </span><span class="default">$string</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">preg_match_all</span><span class="keyword">(</span><span class="string">'/\[([^\]]*)\]/'</span><span class="keyword">, </span><span class="default">$string</span><span class="keyword">, </span><span class="default">$arr_matches</span><span class="keyword">, </span><span class="default">PREG_PATTERN_ORDER</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">$arr</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$arr_matches</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">] as </span><span class="default">$dimension</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">$return</span><span class="keyword">[</span><span class="default">$dimension</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$return</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">$test </span><span class="keyword">= array(</span><span class="string">'one' </span><span class="keyword">=&gt; </span><span class="string">'two'</span><span class="keyword">, </span><span class="string">'four' </span><span class="keyword">=&gt; array(</span><span class="default">8</span><span class="keyword">));<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">'test'</span><span class="keyword">;<br />
</span><span class="default">$bar </span><span class="keyword">= $</span><span class="default">$foo</span><span class="keyword">;<br />
</span><span class="default">$baz </span><span class="keyword">= </span><span class="string">"[one]"</span><span class="keyword">;<br />
</span><span class="default">$var </span><span class="keyword">= </span><span class="default">VariableArray</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">, </span><span class="default">$baz</span><span class="keyword">); </span><span class="comment">//$var now contains 'two'<br />
<br />
</span><span class="default">$baz </span><span class="keyword">= </span><span class="string">"[four][0]"</span><span class="keyword">;<br />
</span><span class="default">$var </span><span class="keyword">= </span><span class="default">VariableArray</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">, </span><span class="default">$baz</span><span class="keyword">); </span><span class="comment">//$var now contains int(8)<br />
</span><span class="default">?&gt;<br />
</span><br />
You can simply pass in a superglobal as the first argument. Note for associative arrays don't put quotes inside the square braces unless you adjust the regexp to accept it. I wanted to keep it simple.</span>
</code></div>
  </div>
 </div>
 <a name="87337"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#87337" class="date">30-Nov-2008 07:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I have a HTML form that has a dynamic number of fields (for entry of collected data it adds a new field each time) and would like to use the variable variable on _POST.&nbsp; This way, I could increment the field name value with a loop limit when say 100 fields are reached (the max for the form.)<br />
<br />
Below is the solution I came up with to work around it:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $MaxRows</span><span class="keyword">=</span><span class="default">100</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$NumbersOfTime</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">extract </span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">,</span><span class="default">EXTR_PREFIX_ALL</span><span class="keyword">,</span><span class="string">'pos'</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt;= </span><span class="default">$MaxRows</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">= </span><span class="default">$i </span><span class="keyword">+ </span><span class="default">1</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$tmp </span><span class="keyword">= </span><span class="string">"pos_TimeRecorded{$i}"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (isset($</span><span class="default">$tmp</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$TimeRecorded</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">]=$</span><span class="default">$tmp</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$NumbersOfTime</span><span class="keyword">=</span><span class="default">$i</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="83658"></a>
 <div class="note">
  <strong class='user'>nullhility at gmail dot com</strong>
  <a href="#83658" class="date">06-Jun-2008 12:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It's also valuable to note the following:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">${</span><span class="default">date</span><span class="keyword">(</span><span class="string">"M"</span><span class="keyword">)} = </span><span class="string">"Worked"</span><span class="keyword">;<br />
echo ${</span><span class="default">date</span><span class="keyword">(</span><span class="string">"M"</span><span class="keyword">)};<br />
</span><span class="default">?&gt;<br />
</span><br />
This is perfectly legal, anything inside the braces is executed first, the return value then becomes the variable name. Echoing the same variable variable using the function that created it results in the same return and therefore the same variable name is used in the echo statement. Have fun ;).</span>
</code></div>
  </div>
 </div>
 <a name="81033"></a>
 <div class="note">
  <strong class='user'>Nathan Hammond</strong>
  <a href="#81033" class="date">11-Feb-2008 03:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
These are the scenarios that you may run into trying to reference superglobals dynamically. Whether or not it works appears to be dependent upon the current scope.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$_POST</span><span class="keyword">[</span><span class="string">'asdf'</span><span class="keyword">] = </span><span class="string">'something'</span><span class="keyword">;<br />
<br />
function </span><span class="default">test</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// NULL -- not what initially expected<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$string </span><span class="keyword">= </span><span class="string">'_POST'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(${</span><span class="default">$string</span><span class="keyword">});<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Works as expected<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(${</span><span class="string">'_POST'</span><span class="keyword">});<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Works as expected<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">global ${</span><span class="default">$string</span><span class="keyword">};<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(${</span><span class="default">$string</span><span class="keyword">});<br />
<br />
}<br />
<br />
</span><span class="comment">// Works as expected<br />
</span><span class="default">$string </span><span class="keyword">= </span><span class="string">'_POST'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(${</span><span class="default">$string</span><span class="keyword">});<br />
<br />
</span><span class="default">test</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80128"></a>
 <div class="note">
  <strong class='user'>j3nda at fv dot cz</strong>
  <a href="#80128" class="date">30-Dec-2007 01:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
hi, i handling multi-array with like this:<br />
<br />
i use this for some classes with direct access to $__info array. and i have some config_{set|get} static functions without this class, but handling is the same.<br />
<br />
i'm not testing this piece of code for benchmark and high load.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">__info </span><span class="keyword">{<br />
&nbsp; private </span><span class="default">$__info</span><span class="keyword">=array();<br />
<br />
&nbsp; public function </span><span class="default">__s</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">=</span><span class="default">null</span><span class="keyword">, </span><span class="default">$id</span><span class="keyword">=</span><span class="string">''</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$id </span><span class="keyword">== </span><span class="string">''</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$id</span><span class="keyword">=</span><span class="string">'[\''</span><span class="keyword">.</span><span class="default">$id</span><span class="keyword">.</span><span class="string">'\']'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">2</span><span class="keyword">, </span><span class="default">$max</span><span class="keyword">=</span><span class="default">func_num_args</span><span class="keyword">(), </span><span class="default">$args</span><span class="keyword">=</span><span class="default">func_get_args</span><span class="keyword">(); </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$max</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$id</span><span class="keyword">.=</span><span class="string">'[\''</span><span class="keyword">.</span><span class="default">$args</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">].</span><span class="string">'\']'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; eval(</span><span class="string">'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (isset($this-&gt;__info'</span><span class="keyword">.</span><span class="default">$id</span><span class="keyword">.</span><span class="string">')) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // debug || vyjimka<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;__info'</span><span class="keyword">.</span><span class="default">$id</span><span class="keyword">.</span><span class="string">'=$value;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; '</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__g</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$uid</span><span class="keyword">=</span><span class="string">''</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">, </span><span class="default">$max</span><span class="keyword">=</span><span class="default">func_num_args</span><span class="keyword">(), </span><span class="default">$args</span><span class="keyword">=</span><span class="default">func_get_args</span><span class="keyword">(); </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$max</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$uid</span><span class="keyword">.=</span><span class="string">"[\'"</span><span class="keyword">.</span><span class="default">$args</span><span class="keyword">[</span><span class="default">$i</span><span class="keyword">].</span><span class="string">"\']"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return eval(</span><span class="string">'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (isset($this-&gt;__info'</span><span class="keyword">.</span><span class="default">$uid</span><span class="keyword">.</span><span class="string">')) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return $this-&gt;__info'</span><span class="keyword">.</span><span class="default">$uid</span><span class="keyword">.</span><span class="string">';<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // debug || vyjimka<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; '</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80065"></a>
 <div class="note">
  <strong class='user'>craigmorey at gmail dot com</strong>
  <a href="#80065" class="date">27-Dec-2007 08:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For a long time I've been trying to use variable variables to figure out how to store and retrieve multi-dimensional arrays in a MySQL dbase. For instance, a config setting stored in a complex array might resemble the below:<br />
<br />
<span class="default">&lt;?php $config</span><span class="keyword">[</span><span class="string">'modules'</span><span class="keyword">][</span><span class="string">'module_events'</span><span class="keyword">][</span><span class="string">'settings'</span><span class="keyword">][</span><span class="string">'template'</span><span class="keyword">][</span><span class="string">'name'</span><span class="keyword">] = </span><span class="string">'List Page'</span><span class="keyword">; </span><span class="default">?&gt;<br />
</span><br />
The most obvious way for storing this info in a dbase (discounting XML/JSON) is to store a "path" (of the nesting) and a "value" in a database record:<br />
<br />
'modules,module_events,settings,template,name' = 'List Page'<br />
<br />
But storing it is only part of the problem. PHP variable variables are no use to try and interpret string representations of arrays, eg it will see the string representation of a nested array such as config['modules']['module_events'] as a single variable called 'config[modules][module_events]', so loops that parse the "path" into a variable variable don't help.<br />
<br />
So here is a little function that parses an array of "paths" and "value" strings (eg from a dbase) into a multi-dimensional nested array.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">multiArrayMe</span><span class="keyword">(</span><span class="default">$input_array</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$output_array </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># common sense check<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if (!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$input_array</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># loop through the array of "path"=&gt;"value"<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">foreach (</span><span class="default">$input_array </span><span class="keyword">AS </span><span class="default">$key1 </span><span class="keyword">=&gt; </span><span class="default">$val1</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># explode the path to find the list of nested keys<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$temp1 </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">','</span><span class="keyword">,</span><span class="default">$key1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># if this path isn't an array, skip this cycle<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$temp1</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># reverse sort the keys so we'll start building from <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; # the bottom, not the top<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">krsort</span><span class="keyword">(</span><span class="default">$temp1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># start with the temporary array off with the end value<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$temp2 </span><span class="keyword">= </span><span class="default">$val1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># loop through the nested keys<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$temp1 </span><span class="keyword">AS </span><span class="default">$val2</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># if this nested key has no name, <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; # (and isn't "0") skip this cycle<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">$val2</span><span class="keyword">===</span><span class="default">false</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># gradually build up the this temporary nested array <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; # from the leaf, working up the branches to the trunk<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$temp2 </span><span class="keyword">= array(</span><span class="default">$val2 </span><span class="keyword">=&gt; </span><span class="default">$temp2</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment"># for this cycle, dump this bucketful of data into the bathtub<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$output_array </span><span class="keyword">= </span><span class="default">array_merge_recursive</span><span class="keyword">(</span><span class="default">$output_array</span><span class="keyword">,</span><span class="default">$temp2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$output_array</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="79460"></a>
 <div class="note">
  <strong class='user'>correojulian33-php at yahoo dot es</strong>
  <a href="#79460" class="date">28-Nov-2007 06:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This example may help to overcome the limitation on $this.<br />
<br />
Populate automatically fields of an object form a $_GET variable.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">pp</span><span class="keyword">{<br />
&nbsp;&nbsp; var </span><span class="default">$prop1</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">,</span><span class="default">$prop2</span><span class="keyword">=</span><span class="default">2</span><span class="keyword">,</span><span class="default">$prop3</span><span class="keyword">=array(</span><span class="default">3</span><span class="keyword">,</span><span class="default">4</span><span class="keyword">,</span><span class="default">5</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; function </span><span class="default">fun1</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$vars</span><span class="keyword">=</span><span class="default">get_class_vars</span><span class="keyword">(</span><span class="string">'pp'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; while(list(</span><span class="default">$var</span><span class="keyword">,</span><span class="default">$value</span><span class="keyword">)=</span><span class="default">each</span><span class="keyword">(</span><span class="default">$vars</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$ref</span><span class="keyword">=&amp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$var</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$ref</span><span class="keyword">=</span><span class="default">$_GET</span><span class="keyword">[</span><span class="default">$var</span><span class="keyword">];<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; } </span><span class="comment">// while<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'prop1'</span><span class="keyword">]=</span><span class="string">"uno"</span><span class="keyword">;<br />
</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'prop2'</span><span class="keyword">]=</span><span class="string">"dos"</span><span class="keyword">;<br />
</span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'prop3'</span><span class="keyword">]=array(</span><span class="string">'tres'</span><span class="keyword">,</span><span class="string">'cuatro'</span><span class="keyword">,</span><span class="string">'cinco'</span><span class="keyword">,</span><span class="string">'seis'</span><span class="keyword">);<br />
<br />
</span><span class="default">$p</span><span class="keyword">=new </span><span class="default">pp</span><span class="keyword">();<br />
</span><span class="default">$p</span><span class="keyword">-&gt;</span><span class="default">fun1</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
output is ...<br />
<br />
object(pp)#1 (3) {<br />
&nbsp; ["prop1"]=&gt;<br />
&nbsp; &amp;string(3) "uno"<br />
&nbsp; ["prop2"]=&gt;<br />
&nbsp; &amp;string(3) "dos"<br />
&nbsp; ["prop3"]=&gt;<br />
&nbsp; &amp;array(4) {<br />
&nbsp;&nbsp;&nbsp; [0]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(4) "tres"<br />
&nbsp;&nbsp;&nbsp; [1]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(6) "cuatro"<br />
&nbsp;&nbsp;&nbsp; [2]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(5) "cinco"<br />
&nbsp;&nbsp;&nbsp; [3]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(4) "seis"<br />
&nbsp; }<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="78466"></a>
 <div class="note">
  <strong class='user'>the_tevildo at yahoo dot com</strong>
  <a href="#78466" class="date">13-Oct-2007 06:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is a handy function I put together to allow variable variables to be used with arrays.<br />
<br />
To use the function, when you want to reference an array, send it in the form 'array:key' rather than 'array[key]'.<br />
<br />
For example:<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">indirect </span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)&nbsp; &nbsp;&nbsp; </span><span class="comment">// Replaces $$var = $value<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="default">$var_data </span><span class="keyword">= </span><span class="default">$explode</span><span class="keyword">(</span><span class="default">$var</span><span class="keyword">, </span><span class="string">':'</span><span class="keyword">);<br />
&nbsp;&nbsp; if (isset(</span><span class="default">$var_data</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]))<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; ${</span><span class="default">$var_data</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]}[</span><span class="default">$var_data</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; else<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; ${</span><span class="default">$var_data</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]} = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$temp_array </span><span class="keyword">= </span><span class="default">array_fill</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">, </span><span class="default">4</span><span class="keyword">, </span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">$temp_var </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
</span><span class="default">$int_var_list </span><span class="keyword">= array(</span><span class="string">'temp_array[2]'</span><span class="keyword">, </span><span class="string">'temp_var'</span><span class="keyword">);<br />
<br />
while (list(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$var_name</span><span class="keyword">) = </span><span class="default">each</span><span class="keyword">(</span><span class="default">$int_var_list</span><span class="keyword">))<br />
{<br />
&nbsp;&nbsp; </span><span class="comment">//&nbsp; Doesn't work - creates scalar variable called "$temp_array[2]" <br />
&nbsp;&nbsp; </span><span class="keyword">$</span><span class="default">$var_name </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$temp_array</span><span class="keyword">);<br />
echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$temp_var</span><span class="keyword">);<br />
echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
<br />
</span><span class="comment">//&nbsp; Does work!<br />
<br />
</span><span class="default">$int_var_list </span><span class="keyword">= array(</span><span class="string">'temp_array:2'</span><span class="keyword">, </span><span class="string">'temp_var'</span><span class="keyword">);<br />
<br />
while (list(</span><span class="default">$key</span><span class="keyword">, </span><span class="default">$var_name</span><span class="keyword">) = </span><span class="default">each</span><span class="keyword">(</span><span class="default">$int_var_list</span><span class="keyword">))<br />
{<br />
&nbsp;&nbsp; </span><span class="default">indirect</span><span class="keyword">(</span><span class="default">$var_name</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$temp_array</span><span class="keyword">);<br />
echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$temp_var</span><span class="keyword">);<br />
echo </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="75669"></a>
 <div class="note">
  <strong class='user'>Sinured</strong>
  <a href="#75669" class="date">11-Jun-2007 06:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
One interesting thing I found out: You can concatenate variables and use spaces. Concatenating constants and function calls are also possible.<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">'ONE'</span><span class="keyword">, </span><span class="default">1</span><span class="keyword">);<br />
function </span><span class="default">one</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">1</span><span class="keyword">;<br />
}<br />
</span><span class="default">$one </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
<br />
${</span><span class="string">"foo$one"</span><span class="keyword">} = </span><span class="string">'foo'</span><span class="keyword">;<br />
echo </span><span class="default">$foo1</span><span class="keyword">; </span><span class="comment">// foo<br />
</span><span class="keyword">${</span><span class="string">'foo' </span><span class="keyword">. </span><span class="default">ONE</span><span class="keyword">} = </span><span class="string">'bar'</span><span class="keyword">; <br />
echo </span><span class="default">$foo1</span><span class="keyword">; </span><span class="comment">// bar<br />
</span><span class="keyword">${</span><span class="string">'foo' </span><span class="keyword">. </span><span class="default">one</span><span class="keyword">()} = </span><span class="string">'baz'</span><span class="keyword">;<br />
echo </span><span class="default">$foo1</span><span class="keyword">; </span><span class="comment">// baz<br />
</span><span class="default">?&gt;<br />
</span><br />
This syntax doesn't work for functions:<br />
<br />
<span class="default">&lt;?php<br />
$foo </span><span class="keyword">= </span><span class="string">'info'</span><span class="keyword">;<br />
{</span><span class="string">"php$foo"</span><span class="keyword">}(); </span><span class="comment">// Parse error<br />
<br />
// You'll have to do:<br />
</span><span class="default">$func </span><span class="keyword">= </span><span class="string">"php$foo"</span><span class="keyword">;<br />
</span><span class="default">$func</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Note: Don't leave out the quotes on strings inside the curly braces, PHP won't handle that graciously.</span>
</code></div>
  </div>
 </div>
 <a name="65685"></a>
 <div class="note">
  <strong class='user'>mot at tdvniikp dot ru</strong>
  <a href="#65685" class="date">05-May-2006 08:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can simple access Globals by variable variables in functions, example:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">abc</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$context </span><span class="keyword">= </span><span class="string">'_SESSION'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; global $</span><span class="default">$context</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if(isset($</span><span class="default">$context</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">($</span><span class="default">$context</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">abc</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="57684"></a>
 <div class="note">
  <strong class='user'>fabio at noc dot soton dot ac dot uk</strong>
  <a href="#57684" class="date">11-Oct-2005 04:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A static variable variable sounds like an oxymoron and indeed cannot exist. If you define:<br />
<br />
<span class="default">&lt;?php<br />
$var </span><span class="keyword">= </span><span class="string">"ciao"</span><span class="keyword">;<br />
static $</span><span class="default">$var </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
you get a parse error.<br />
Regards,<br />
<br />
Fabio</span>
</code></div>
  </div>
 </div>
 <a name="51154"></a>
 <div class="note">
  <strong class='user'>rafael at fuchs inf br</strong>
  <a href="#51154" class="date">21-Mar-2005 08:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can use constants in variable variables, like I show below. This works fine:<br />
<br />
<span class="default">&lt;?php<br />
define</span><span class="keyword">(</span><span class="string">"TEST"</span><span class="keyword">,</span><span class="string">"Fuchs"</span><span class="keyword">);<br />
</span><span class="default">$Fuchs </span><span class="keyword">= </span><span class="string">"Test"</span><span class="keyword">;<br />
<br />
echo </span><span class="default">TEST </span><span class="keyword">. </span><span class="string">"&lt;BR&gt;"</span><span class="keyword">;<br />
echo ${</span><span class="default">TEST</span><span class="keyword">};<br />
</span><span class="default">?&gt;<br />
</span><br />
output:<br />
<br />
Fuchs<br />
Test</span>
</code></div>
  </div>
 </div>
 <a name="50912"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#50912" class="date">13-Mar-2005 07:25</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
It may be worth specifically noting, if variable names follow some kind of "template," they can be referenced like this:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Given these variables ...<br />
</span><span class="default">$nameTypes&nbsp; &nbsp; </span><span class="keyword">= array(</span><span class="string">"first"</span><span class="keyword">, </span><span class="string">"last"</span><span class="keyword">, </span><span class="string">"company"</span><span class="keyword">);<br />
</span><span class="default">$name_first&nbsp;&nbsp; </span><span class="keyword">= </span><span class="string">"John"</span><span class="keyword">;<br />
</span><span class="default">$name_last&nbsp; &nbsp; </span><span class="keyword">= </span><span class="string">"Doe"</span><span class="keyword">;<br />
</span><span class="default">$name_company </span><span class="keyword">= </span><span class="string">"PHP.net"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// Then this loop is ...<br />
</span><span class="keyword">foreach(</span><span class="default">$nameTypes </span><span class="keyword">as </span><span class="default">$type</span><span class="keyword">)<br />
&nbsp; print ${</span><span class="string">"name_$type"</span><span class="keyword">} . </span><span class="string">"\n"</span><span class="keyword">;<br />
<br />
</span><span class="comment">// ... equivalent to this print statement.<br />
</span><span class="keyword">print </span><span class="string">"$name_first\n$name_last\n$name_company\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This is apparent from the notes others have left, but is not explicitly stated.</span>
</code></div>
  </div>
 </div>
 <a name="50529"></a>
 <div class="note">
  <strong class='user'>Shawn Beltz</strong>
  <a href="#50529" class="date">02-Mar-2005 12:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Multidimensional variable variables.&nbsp; If you want to run the below as one big program, you'll have to undefine $foo in between assignments.<br />
<br />
<span class="default">&lt;?php<br />
<br />
$foo </span><span class="keyword">= </span><span class="string">"this is foo."</span><span class="keyword">;<br />
</span><span class="default">$ref </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
print $</span><span class="default">$ref</span><span class="keyword">;<br />
</span><span class="comment"># prints "this is foo."<br />
<br />
</span><span class="default">$foo</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'a_z'</span><span class="keyword">] = </span><span class="string">"this is foo[1][a_z]."</span><span class="keyword">;<br />
</span><span class="default">$ref </span><span class="keyword">= </span><span class="string">"foo[1][a_z]"</span><span class="keyword">;<br />
print $</span><span class="default">$ref</span><span class="keyword">;<br />
</span><span class="comment"># Doesn't print anything!<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"this is foo."</span><span class="keyword">;<br />
</span><span class="default">$ref </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">$erf </span><span class="keyword">= eval(</span><span class="string">"return \$$ref;"</span><span class="keyword">);<br />
print </span><span class="default">$erf</span><span class="keyword">;<br />
</span><span class="comment"># prints "this is foo."<br />
<br />
</span><span class="default">$foo</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="string">'a_z'</span><span class="keyword">] = </span><span class="string">"this is foo[1][a_z]."</span><span class="keyword">;<br />
</span><span class="default">$ref </span><span class="keyword">= </span><span class="string">"foo[1][a_z]"</span><span class="keyword">;<br />
</span><span class="default">$erf </span><span class="keyword">= eval(</span><span class="string">"return \$$ref;"</span><span class="keyword">);<br />
print </span><span class="default">$erf</span><span class="keyword">;<br />
</span><span class="comment"># prints "this is foo[1][a_z]."<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="31857"></a>
 <div class="note">
  <strong class='user'>sir_hmba AT yahoo DOT com</strong>
  <a href="#31857" class="date">06-May-2003 03:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This is somewhat redundant, but I didn't see an example that combined dynamic reference of *both* object and attribute names.<br />
<br />
Here's the code:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$bar</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$baz</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">foo</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">bar </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">baz </span><span class="keyword">= </span><span class="default">6</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$f </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
echo </span><span class="string">"f-&gt;bar=$f-&gt;bar&nbsp; f-&gt;baz=$f-&gt;baz\n"</span><span class="keyword">;<br />
<br />
</span><span class="default">$obj&nbsp; </span><span class="keyword">= </span><span class="string">'f'</span><span class="keyword">;<br />
</span><span class="default">$attr </span><span class="keyword">= </span><span class="string">'bar'</span><span class="keyword">;<br />
</span><span class="default">$val&nbsp; </span><span class="keyword">= $</span><span class="default">$obj</span><span class="keyword">-&gt;{</span><span class="default">$attr</span><span class="keyword">};<br />
<br />
echo </span><span class="string">"obj=$obj&nbsp; attr=$attr&nbsp; val=$val\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
And here's the output:<br />
<br />
f-&gt;bar=3&nbsp; f-&gt;baz=6<br />
$obj=f&nbsp; $attr=bar&nbsp; $val=3</span>
</code></div>
  </div>
 </div>
 <a name="25314"></a>
 <div class="note">
  <strong class='user'>antony dot booth at nodomain dot here</strong>
  <a href="#25314" class="date">19-Sep-2002 07:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You may think of using variable variables to dynamically generate variables from an array, by doing something similar to: -<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;</span><span class="keyword">foreach (</span><span class="default">$array </span><span class="keyword">as </span><span class="default">$key </span><span class="keyword">=&gt; </span><span class="default">$value</span><span class="keyword">) <br />
&nbsp;{<br />
&nbsp; $</span><span class="default">$key</span><span class="keyword">= </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
This however would be reinventing the wheel when you can simply use: <br />
<br />
<span class="default">&lt;?php<br />
extract</span><span class="keyword">( </span><span class="default">$array</span><span class="keyword">, </span><span class="default">EXTR_OVERWRITE</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that this will overwrite the contents of variables that already exist.<br />
<br />
Extract has useful functionality to prevent this, or you may group the variables by using prefixes too, so you could use: -<br />
<br />
EXTR_PREFIX_ALL<br />
<br />
<span class="default">&lt;?php<br />
$array </span><span class="keyword">=array(</span><span class="string">"one" </span><span class="keyword">=&gt; </span><span class="string">"First Value"</span><span class="keyword">,<br />
</span><span class="string">"two" </span><span class="keyword">=&gt; </span><span class="string">"2nd Value"</span><span class="keyword">,<br />
</span><span class="string">"three" </span><span class="keyword">=&gt; </span><span class="string">"8"<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
</span><span class="default">extract</span><span class="keyword">( </span><span class="default">$array</span><span class="keyword">, </span><span class="default">EXTR_PREFIX_ALL</span><span class="keyword">, </span><span class="string">"my_prefix_"</span><span class="keyword">);<br />
&nbsp;&nbsp; <br />
</span><span class="default">?&gt;<br />
</span><br />
This would create variables: -<br />
$my_prefix_one <br />
$my_prefix_two<br />
$my_prefix_three<br />
<br />
containing: -<br />
"First Value", "2nd Value" and "8" respectively</span>
</code></div>
  </div>
 </div>
 <a name="25023"></a>
 <div class="note">
  <strong class='user'>jupp-mueller at t-online dot de</strong>
  <a href="#25023" class="date">08-Sep-2002 06:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I found another undocumented/cool feature: variable member variables in classes. It's pretty easy:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
&nbsp; function </span><span class="default">bar</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$bar1 </span><span class="keyword">= </span><span class="string">"var1"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$bar2 </span><span class="keyword">= </span><span class="string">"var2"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$bar1</span><span class="keyword">}= </span><span class="string">"this "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$bar2</span><span class="keyword">} = </span><span class="string">"works"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$test </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">;<br />
</span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">bar</span><span class="keyword">();<br />
echo </span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">var1 </span><span class="keyword">. </span><span class="default">$test</span><span class="keyword">-&gt;</span><span class="default">var2</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="24534"></a>
 <div class="note">
  <strong class='user'>thien_tmpNOSPAM at hotmail dot com</strong>
  <a href="#24534" class="date">20-Aug-2002 03:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can also use variable variables and the string concat operator to generate suffixed (or prefixed) variables based on a base name.<br />
<br />
For instance, if you wanted to dynamically generate this series of variables:<br />
<br />
base1_suffix1<br />
base1_suffix2<br />
base2_suffix1<br />
base2_suffix2<br />
base3_suffix1<br />
base3_suffix2<br />
<br />
You can do this:<br />
<br />
<span class="default">&lt;?php<br />
$bases </span><span class="keyword">= array(</span><span class="string">'base1'</span><span class="keyword">, </span><span class="string">'base2'</span><span class="keyword">, </span><span class="string">'base3'</span><span class="keyword">);<br />
</span><span class="default">$suffixes </span><span class="keyword">= array(</span><span class="string">'suffix1'</span><span class="keyword">, </span><span class="default">suffix2</span><span class="keyword">);<br />
foreach(</span><span class="default">$bases </span><span class="keyword">as </span><span class="default">$base</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; foreach(</span><span class="default">$suffixes </span><span class="keyword">as </span><span class="default">$suffix</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; ${</span><span class="default">$base</span><span class="keyword">.</span><span class="default">$suffix</span><span class="keyword">} = </span><span class="string">"whatever"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">#...etc<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="24296"></a>
 <div class="note">
  <strong class='user'>J. Dyer</strong>
  <a href="#24296" class="date">12-Aug-2002 01:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Another use for this feature in PHP is dynamic parsing..&nbsp; <br />
<br />
Due to the rather odd structure of an input string I am currently parsing, I must have a reference for each particular object instantiation in the order which they were created.&nbsp; In addition, because of the syntax of the input string, elements of the previous object creation are required for the current one.&nbsp; <br />
<br />
Normally, you won't need something this convolute.&nbsp; In this example, I needed to load an array with dynamically named objects - (yes, this has some basic Object Oriented programming, please bare with me..)<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="keyword">include(</span><span class="string">"obj.class"</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp; </span><span class="comment">// this is only a skeletal example, of course.<br />
&nbsp;&nbsp; </span><span class="default">$object_array </span><span class="keyword">= array();<br />
<br />
&nbsp;&nbsp; </span><span class="comment">// assume the $input array has tokens for parsing.<br />
&nbsp;&nbsp; </span><span class="keyword">foreach (</span><span class="default">$input_array </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt;</span><span class="default">$value</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// test to ensure the $value is what we need.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$obj </span><span class="keyword">= </span><span class="string">"obj"</span><span class="keyword">.</span><span class="default">$key</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; $</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">Obj</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">, </span><span class="default">$other_var</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">Array_Push</span><span class="keyword">(</span><span class="default">$object_array</span><span class="keyword">, $</span><span class="default">$obj</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="comment">// etc..<br />
&nbsp;&nbsp; </span><span class="keyword">}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Now, we can use basic array manipulation to get these objects out in the particular order we need, and the objects no longer are dependant on the previous ones.<br />
<br />
I haven't fully tested the implimentation of the objects.&nbsp; The&nbsp; scope of a variable-variable's object attributes (get all that?) is a little tough to crack.&nbsp; Regardless, this is another example of the manner in which the var-vars can be used with precision where tedious, extra hard-coding is the only alternative.<br />
<br />
Then, we can easily pull everything back out again using a basic array function: foreach.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//...<br />
&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$array </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">=&gt;</span><span class="default">$object</span><span class="keyword">){<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="default">$key</span><span class="keyword">.</span><span class="string">" -- "</span><span class="keyword">.</span><span class="default">$object</span><span class="keyword">-&gt;</span><span class="default">print_fcn</span><span class="keyword">().</span><span class="string">" &lt;br/&gt;\n"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; } </span><span class="comment">// end foreach&nbsp;&nbsp; <br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Through this, we can pull a dynamically named object out of the array it was stored in without actually knowing its name.</span>
</code></div>
  </div>
 </div>
 <a name="22037"></a>
 <div class="note">
  <a href="#22037" class="date">04-Jun-2002 11:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The 'dollar dereferencing' (to coin a phrase) doesn't seem to be limited to two layers, even without curly braces.&nbsp; Observe:<br />
<br />
<span class="default">&lt;?php<br />
$one </span><span class="keyword">= </span><span class="string">"two"</span><span class="keyword">;<br />
</span><span class="default">$two </span><span class="keyword">= </span><span class="string">"three"</span><span class="keyword">;<br />
</span><span class="default">$three </span><span class="keyword">= </span><span class="string">"four"</span><span class="keyword">;<br />
</span><span class="default">$four </span><span class="keyword">= </span><span class="string">"five"</span><span class="keyword">;<br />
echo $$$</span><span class="default">$one</span><span class="keyword">; </span><span class="comment">//prints 'five'.<br />
</span><span class="default">?&gt;<br />
</span><br />
This works for L-values as well.&nbsp; So the below works the same way:<br />
<br />
<span class="default">&lt;?php<br />
$one </span><span class="keyword">= </span><span class="string">"two"</span><span class="keyword">;<br />
$</span><span class="default">$one </span><span class="keyword">= </span><span class="string">"three"</span><span class="keyword">;<br />
$$</span><span class="default">$one </span><span class="keyword">= </span><span class="string">"four"</span><span class="keyword">;<br />
$$$</span><span class="default">$one </span><span class="keyword">= </span><span class="string">"five"</span><span class="keyword">;<br />
echo $$$</span><span class="default">$one</span><span class="keyword">; </span><span class="comment">//still prints 'five'.<br />
</span><span class="default">?&gt;<br />
</span><br />
NOTE: Tested on PHP 4.2.1, Apache 2.0.36, Red Hat 7.2</span>
</code></div>
  </div>
 </div>
 <a name="14664"></a>
 <div class="note">
  <strong class='user'>chrisNOSPAM at kampmeier dot net</strong>
  <a href="#14664" class="date">08-Aug-2001 10:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Note that normal variable variables will not be parsed in double-quoted strings. You'll have to use the braces to make it work, to resolve the ambiguity. For example:<br />
<br />
<span class="default">&lt;?php<br />
$varname </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="string">"bar"</span><span class="keyword">;<br />
<br />
print $</span><span class="default">$varname</span><span class="keyword">;&nbsp; </span><span class="comment">// Prints "bar"<br />
</span><span class="keyword">print </span><span class="string">"$$varname"</span><span class="keyword">;&nbsp; </span><span class="comment">// Prints "$foo"<br />
</span><span class="keyword">print </span><span class="string">"${$varname}"</span><span class="keyword">; </span><span class="comment">// Prints "bar"<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="12841"></a>
 <div class="note">
  <strong class='user'>mstearne at entermix dot com</strong>
  <a href="#12841" class="date">10-May-2001 06:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Variable variables techniques do not work when one of the "variables" is a constant.&nbsp; The example below illustrates this.&nbsp; This is probably the desired behavior for constants, but was confusing for me when I was trying to figure it out.&nbsp; The alternative I used was to add the variables I needed to the $GLOBALS array instead of defining them as constants. <br />
<br />
<span class="default">&lt;?php<br />
<br />
define</span><span class="keyword">(</span><span class="string">"DB_X_NAME"</span><span class="keyword">,</span><span class="string">"database1"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"DB_Y_NAME"</span><span class="keyword">,</span><span class="string">"database2"</span><span class="keyword">);<br />
</span><span class="default">$DB_Z_NAME</span><span class="keyword">=</span><span class="string">"database3"</span><span class="keyword">;<br />
<br />
<br />
function </span><span class="default">connectTo</span><span class="keyword">(</span><span class="default">$databaseName</span><span class="keyword">){<br />
global </span><span class="default">$DB_Z_NAME</span><span class="keyword">;<br />
<br />
</span><span class="default">$fullDatabaseName</span><span class="keyword">=</span><span class="string">"DB_"</span><span class="keyword">.</span><span class="default">$databaseName</span><span class="keyword">.</span><span class="string">"_NAME"</span><span class="keyword">;<br />
return ${</span><span class="default">$fullDatabaseName</span><span class="keyword">};<br />
<br />
}<br />
<br />
print </span><span class="string">"DB_X_NAME is "</span><span class="keyword">.</span><span class="default">connectTo</span><span class="keyword">(</span><span class="string">"X"</span><span class="keyword">).</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
print </span><span class="string">"DB_Y_NAME is "</span><span class="keyword">.</span><span class="default">connectTo</span><span class="keyword">(</span><span class="string">"Y"</span><span class="keyword">).</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
print </span><span class="string">"DB_Z_NAME is "</span><span class="keyword">.</span><span class="default">connectTo</span><span class="keyword">(</span><span class="string">"Z"</span><span class="keyword">).</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span>[Editor Note: For variable constants, use constant() --Philip]</span>
</code></div>
  </div>
 </div>
 <a name="11585"></a>
 <div class="note">
  <strong class='user'>bpotier at edreamers dot org</strong>
  <a href="#11585" class="date">26-Feb-2001 09:11</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A good example of the use of variable variables name. Imagine that you want to modify at the same time a list of more than one record from a db table.<br />
1) You can easily create a dynamic form using PHP. Name your form elements using a static name and the record id<br />
ex: &lt;input name="aninput<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">$recordid?&gt;</span>" which gives in the output something like &lt;input name="aninput15"&gt;<br />
<br />
2)You need to provide to your form action/submit script the list of records ids via an array serialized and urlencoded via an hidden field (to decode and un serialize once in the submit script)<br />
<br />
3) In the script used to submit you form you can access the input value by using the variable ${'aninput'.$recordid} to dynamically create as many UPDATE query as you need<br />
<br />
[Editor Note: Simply use an array instead, for example: &lt;input name="aninput[<span class="default">&lt;?php </span><span class="keyword">echo </span><span class="default">$recordid?&gt;</span>]" And loop through that array. -Philip]</span>
</code></div>
  </div>
 </div>
 <a name="10034"></a>
 <div class="note">
  <strong class='user'>dnl at au dot ru</strong>
  <a href="#10034" class="date">08-Dec-2000 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
By the way...<br />
Variable variables can be used as pointers to objects' properties:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">someclass </span><span class="keyword">{<br />
&nbsp; var </span><span class="default">$a </span><span class="keyword">= </span><span class="string">"variable a"</span><span class="keyword">;<br />
&nbsp; var </span><span class="default">$b </span><span class="keyword">= </span><span class="string">"another variable: b"</span><span class="keyword">;<br />
&nbsp; }<br />
<br />
</span><span class="default">$c </span><span class="keyword">= new </span><span class="default">someclass</span><span class="keyword">;<br />
</span><span class="default">$d </span><span class="keyword">= </span><span class="string">"b"</span><span class="keyword">;<br />
echo </span><span class="default">$c</span><span class="keyword">-&gt;{</span><span class="default">$d</span><span class="keyword">};<br />
</span><span class="default">?&gt;<br />
</span><br />
outputs: another variable: b</span>
</code></div>
  </div>
 </div>
 <a name="9451"></a>
 <div class="note">
  <strong class='user'>mccoyj at mail dot utexas dot edu</strong>
  <a href="#9451" class="date">03-Nov-2000 03:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
There is no need for the braces for variable object names...they are only needed by an ambiguity arises concerning which part of the reference is variable...usually with arrays.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Schlemiel </span><span class="keyword">{<br />
var </span><span class="default">$aVar </span><span class="keyword">= </span><span class="string">"foo"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$schlemiel </span><span class="keyword">= new </span><span class="default">Schlemiel</span><span class="keyword">;<br />
</span><span class="default">$a </span><span class="keyword">= </span><span class="string">"schlemiel"</span><span class="keyword">;<br />
echo $</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">aVar</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
This code outputs "foo" using PHP 4.0.3.<br />
<br />
Hope this helps...<br />
- Jordan</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.variables.variable&amp;redirect=@w{QGD8CT77}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.variable&amp;redirect=@w{QGD8CT77}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.variables.variable.php">show source</a> |
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