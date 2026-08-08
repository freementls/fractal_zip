<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Anonymous functions - Manual</title>
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
 <link rel="prev" href="functions.internal.php" />
 <link rel="next" href="language.oop5.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/functions.anonymous" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/functions.anonymous.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/manual/en/functions.anonymous.php" />
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
 <li><a href="functions.returning-values.php">Returning values</a></li>
 <li><a href="functions.variable-functions.php">Variable functions</a></li>
 <li><a href="functions.internal.php">Internal (built-in) functions</a></li>
 <li class="active"><a href="functions.anonymous.php">Anonymous functions</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.oop5.php">Classes and Objects<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.internal.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Internal (built-in) functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.anonymous.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/functions.anonymous.php">Brazilian Portuguese</option>
    <option value="zh/functions.anonymous.php">Chinese (Simplified)</option>
    <option value="fr/functions.anonymous.php">French</option>
    <option value="de/functions.anonymous.php">German</option>
    <option value="ja/functions.anonymous.php">Japanese</option>
    <option value="pl/functions.anonymous.php">Polish</option>
    <option value="ro/functions.anonymous.php">Romanian</option>
    <option value="ru/functions.anonymous.php">Russian</option>
    <option value="fa/functions.anonymous.php">Persian</option>
    <option value="es/functions.anonymous.php">Spanish</option>
    <option value="tr/functions.anonymous.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="functions.anonymous" class="sect1">
   <h2 class="title">Anonymous functions</h2>

   <p class="simpara">
    Anonymous functions, also known as <em>closures</em>, allow the
    creation of functions which have no specified name. They are most useful as
    the value of <a href="language.pseudo-types.php#language.types.callback" class="link">callback</a>
    parameters, but they have many other uses.
   </p>

   <div class="example" id="example-161">
    <p><strong>Example #1 Anonymous function example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">preg_replace_callback</span><span style="color: #007700">(</span><span style="color: #DD0000">'~-([a-z])~'</span><span style="color: #007700">,&nbsp;function&nbsp;(</span><span style="color: #0000BB">$match</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">strtoupper</span><span style="color: #007700">(</span><span style="color: #0000BB">$match</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">]);<br />},&nbsp;</span><span style="color: #DD0000">'hello-world'</span><span style="color: #007700">);<br /></span><span style="color: #FF8000">//&nbsp;outputs&nbsp;helloWorld<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>

   <p class="simpara">
    Closures can also be used as the values of variables; PHP automatically 
    converts such expressions into instances of the
    <a href="class.closure.php" class="classname">Closure</a> internal class. Assigning a closure to a
    variable uses the same syntax as any other assignment, including the
    trailing semicolon:
   </p>

   <div class="example" id="example-162">
    <p><strong>Example #2 Anonymous function variable assignment example</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$greet&nbsp;</span><span style="color: #007700">=&nbsp;function(</span><span style="color: #0000BB">$name</span><span style="color: #007700">)<br />{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">printf</span><span style="color: #007700">(</span><span style="color: #DD0000">"Hello&nbsp;%s\r\n"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$name</span><span style="color: #007700">);<br />};<br /><br /></span><span style="color: #0000BB">$greet</span><span style="color: #007700">(</span><span style="color: #DD0000">'World'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$greet</span><span style="color: #007700">(</span><span style="color: #DD0000">'PHP'</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   
   <p class="simpara">
    Closures may also inherit variables from the parent scope. Any such
    variables must be declared in the function header. Inheriting variables from
    the parent scope is <em class="emphasis">not</em> the same as using global
    variables. Global variables exist in the global scope, which is the same no
    matter what function is executing. The parent scope of a closure is the
    function in which the closure was declared (not necessarily the function it
    was called from). See the following example:
   </p>

   <div class="example" id="example-163">
    <p><strong>Example #3 Closures and scoping</strong></p>
    <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #FF8000">//&nbsp;A&nbsp;basic&nbsp;shopping&nbsp;cart&nbsp;which&nbsp;contains&nbsp;a&nbsp;list&nbsp;of&nbsp;added&nbsp;products<br />//&nbsp;and&nbsp;the&nbsp;quantity&nbsp;of&nbsp;each&nbsp;product.&nbsp;Includes&nbsp;a&nbsp;method&nbsp;which<br />//&nbsp;calculates&nbsp;the&nbsp;total&nbsp;price&nbsp;of&nbsp;the&nbsp;items&nbsp;in&nbsp;the&nbsp;cart&nbsp;using&nbsp;a<br />//&nbsp;closure&nbsp;as&nbsp;a&nbsp;callback.<br /></span><span style="color: #007700">class&nbsp;</span><span style="color: #0000BB">Cart<br /></span><span style="color: #007700">{<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">PRICE_BUTTER&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1.00</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">PRICE_MILK&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">3.00</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;const&nbsp;</span><span style="color: #0000BB">PRICE_EGGS&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">6.95</span><span style="color: #007700">;<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;protected&nbsp;</span><span style="color: #0000BB">$products&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #0000BB">$product</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$quantity</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">products</span><span style="color: #007700">[</span><span style="color: #0000BB">$product</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">$quantity</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getQuantity</span><span style="color: #007700">(</span><span style="color: #0000BB">$product</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;isset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">products</span><span style="color: #007700">[</span><span style="color: #0000BB">$product</span><span style="color: #007700">])&nbsp;?&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">products</span><span style="color: #007700">[</span><span style="color: #0000BB">$product</span><span style="color: #007700">]&nbsp;:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">FALSE</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;</span><span style="color: #0000BB">getTotal</span><span style="color: #007700">(</span><span style="color: #0000BB">$tax</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$total&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0.00</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$callback&nbsp;</span><span style="color: #007700">=<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;function&nbsp;(</span><span style="color: #0000BB">$quantity</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$product</span><span style="color: #007700">)&nbsp;use&nbsp;(</span><span style="color: #0000BB">$tax</span><span style="color: #007700">,&nbsp;&amp;</span><span style="color: #0000BB">$total</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$pricePerItem&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">constant</span><span style="color: #007700">(</span><span style="color: #0000BB">__CLASS__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"::PRICE_"&nbsp;</span><span style="color: #007700">.<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">strtoupper</span><span style="color: #007700">(</span><span style="color: #0000BB">$product</span><span style="color: #007700">));<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$total&nbsp;</span><span style="color: #007700">+=&nbsp;(</span><span style="color: #0000BB">$pricePerItem&nbsp;</span><span style="color: #007700">*&nbsp;</span><span style="color: #0000BB">$quantity</span><span style="color: #007700">)&nbsp;*&nbsp;(</span><span style="color: #0000BB">$tax&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">1.0</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;};<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">array_walk</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">products</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$callback</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">round</span><span style="color: #007700">(</span><span style="color: #0000BB">$total</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br /><br /></span><span style="color: #0000BB">$my_cart&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">Cart</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;Add&nbsp;some&nbsp;items&nbsp;to&nbsp;the&nbsp;cart<br /></span><span style="color: #0000BB">$my_cart</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'butter'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$my_cart</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'milk'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$my_cart</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">add</span><span style="color: #007700">(</span><span style="color: #DD0000">'eggs'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">6</span><span style="color: #007700">);<br /><br /></span><span style="color: #FF8000">//&nbsp;Print&nbsp;the&nbsp;total&nbsp;with&nbsp;a&nbsp;5%&nbsp;sales&nbsp;tax.<br /></span><span style="color: #007700">print&nbsp;</span><span style="color: #0000BB">$my_cart</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getTotal</span><span style="color: #007700">(</span><span style="color: #0000BB">0.05</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br /></span><span style="color: #FF8000">//&nbsp;The&nbsp;result&nbsp;is&nbsp;54.29<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
    </div>

   </div>
   
   <p class="simpara">
    Anonymous functions are implemented using the <a href="class.closure.php" class="link">
    <a href="class.closure.php" class="classname">Closure</a></a> class.
   </p>
   
   <div class="sect2">
    <h3 class="title">Changelog</h3>
    <p class="para">
     <table class="doctable informaltable">
      
       <thead>
        <tr>
         <th>Version</th>
         <th>Description</th>
        </tr>

       </thead>

       <tbody class="tbody">
        <tr>
         <td>5.4.0</td>
         <td>
          <var class="varname"><var class="varname">$this</var></var> can be used in anonymous functions.
         </td>
        </tr>

        <tr>
         <td>5.3.0</td>
         <td>
          Anonymous functions become available.
         </td>
        </tr>

       </tbody>
      
     </table>

    </p>
   </div>

   <div class="sect2">
    <h3 class="title">Notes</h3>
    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <span class="simpara">
      It is possible to use  <span class="function"><a href="function.func-num-args.php" class="function">func_num_args()</a></span>,
       <span class="function"><a href="function.func-get-arg.php" class="function">func_get_arg()</a></span>, and  <span class="function"><a href="function.func-get-args.php" class="function">func_get_args()</a></span>
      from within a closure.
     </span>
    </p></blockquote>
   </div>

  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.oop5.php">Classes and Objects<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="functions.internal.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Internal (built-in) functions</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/functions.anonymous.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=functions.anonymous&amp;redirect=http://www.php.net/manual/en/functions.anonymous.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.anonymous&amp;redirect=http://www.php.net/manual/en/functions.anonymous.php">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Anonymous functions</strong>
 </div><div id="allnotes">
 <a name="107949"></a>
 <div class="note">
  <strong class='user'>orwellophile at phpblue dot net</strong>
  <a href="#107949" class="date">16-Mar-2012 04:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can create a dynamic method with a class, with access to member variables, with a little bit of trickery:<br />
<br />
&lt;?<br />
&nbsp;&nbsp;&nbsp; class DynamicFunction {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; var $functionPointer;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; var $mv = "The Member Variable";<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function __construct() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $this-&gt;functionPointer = function($arg) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return sprintf("I am the default closure, argument is %s\n", $arg);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function changeFunction($functionSource) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $functionSource = str_replace('$this', '$_this', $functionSource);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $_this = clone $this;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $f = '$this-&gt;functionPointer = function($arg) use ($_this) {' . PHP_EOL;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $f.= $functionSource . PHP_EOL . "};";<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; eval($f);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function __call($method, $args) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ( $this-&gt;{$method} instanceof Closure ) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return call_user_func_array($this-&gt;{$method},$args);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; throw new Exception("Invalid Function");<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; if (!empty($argc) &amp;&amp; !strcmp(basename($argv[0]), basename(__FILE__))) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $dfstring1 = 'return sprintf("I am dynamic function 1, argument is %s, member variables is %s\n", $arg, $this-&gt;mv);';<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $dfstring2 = 'return sprintf("I am dynamic function 2, argument is %s, member variables is %s\n", $arg, $this-&gt;mv);';<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $df = new DynamicFunction();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $df-&gt;changeFunction($dfstring1);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $df-&gt;functionPointer("Rabbit");<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $df-&gt;changeFunction($dfstring2);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; $df-&gt;mv = "A different var";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo $df-&gt;functionPointer("Cow");<br />
&nbsp;&nbsp;&nbsp; };<br />
?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="107245"></a>
 <div class="note">
  <strong class='user'>mike at borft dot student dot utwente dot nl</strong>
  <a href="#107245" class="date">24-Jan-2012 02:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Since it is possible to assign closures to class variables, it is a shame it is not possible to call them directly. ie. the following does not work:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
<br />
&nbsp; public </span><span class="default">test</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= function(</span><span class="default">$a</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; print </span><span class="string">"$a\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; };<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$f </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
<br />
</span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
However, it is possible using the magic __call function:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">foo </span><span class="keyword">{<br />
<br />
&nbsp; public </span><span class="default">test</span><span class="keyword">;<br />
<br />
&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= function(</span><span class="default">$a</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; print </span><span class="string">"$a\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; };<br />
&nbsp; }<br />
<br />
&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$method</span><span class="keyword">} instanceof </span><span class="default">Closure </span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;{</span><span class="default">$method</span><span class="keyword">},</span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">parent</span><span class="keyword">::</span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
}<br />
</span><span class="default">$f </span><span class="keyword">= new </span><span class="default">foo</span><span class="keyword">();<br />
</span><span class="default">$f</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span>it <br />
Hope it helps someone ;)</span>
</code></div>
  </div>
 </div>
 <a name="106046"></a>
 <div class="note">
  <strong class='user'>lennymail at nospam dot gmail dot com</strong>
  <a href="#106046" class="date">05-Oct-2011 10:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A practical example of anonymous functions could be a clean template system. Here you can completely remove messy HTML from your code and keep everything very readable. The last statement that prints the HTML in the example below resembles LISP or other functional type languages. The closure retains the HTML tag from parent scope(which can be initialized in creative ways) and properly closes it after the function call. Items can either nested or composed within HTML tags.<br />
<br />
You can take this idea further and "map and wrap" columns and rows of recordsets with HTML for display. All that is missing now is the CSS to make it attractive in the browser.<br />
<br />
Closures can be thought of as a very primitive class that has one "apply" method with some private and public data. Functional programming has been around since the first programming languages in the 1950s.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">html </span><span class="keyword">(</span><span class="default">$code </span><span class="keyword">, </span><span class="default">$id</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">, </span><span class="default">$class</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$id </span><span class="keyword">!== </span><span class="string">""</span><span class="keyword">) </span><span class="default">$id </span><span class="keyword">= </span><span class="string">" id = \"$id\"" </span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$class </span><span class="keyword">=&nbsp; (</span><span class="default">$class </span><span class="keyword">!== </span><span class="string">""</span><span class="keyword">)? </span><span class="string">" class =\"$class\""</span><span class="keyword">:</span><span class="string">"&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$open </span><span class="keyword">= </span><span class="string">"&lt;$code$id$class"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$close </span><span class="keyword">= </span><span class="string">"&lt;/$code&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return function (</span><span class="default">$inner </span><span class="keyword">= </span><span class="string">""</span><span class="keyword">) use (</span><span class="default">$open</span><span class="keyword">, </span><span class="default">$close</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"$open$inner$close"</span><span class="keyword">;};<br />
<br />
}<br />
</span><span class="default">$layout </span><span class="keyword">= array(</span><span class="string">'container'</span><span class="keyword">,</span><span class="string">'header'</span><span class="keyword">,</span><span class="string">'pmain'</span><span class="keyword">,</span><span class="string">'lsidebar'</span><span class="keyword">,</span><span class="string">'rsidebar'</span><span class="keyword">,</span><span class="string">'footer'</span><span class="keyword">);<br />
<br />
foreach (</span><span class="default">$layout </span><span class="keyword">as </span><span class="default">$element</span><span class="keyword">)<br />
&nbsp;&nbsp; $</span><span class="default">$element </span><span class="keyword">= </span><span class="default">html </span><span class="keyword">(</span><span class="string">"div"</span><span class="keyword">, </span><span class="default">$element</span><span class="keyword">);<br />
<br />
</span><span class="default">$div </span><span class="keyword">= </span><span class="default">html</span><span class="keyword">(</span><span class="string">"div"</span><span class="keyword">, </span><span class="string">"test"</span><span class="keyword">);<br />
<br />
</span><span class="default">$bold </span><span class="keyword">= </span><span class="default">html</span><span class="keyword">(</span><span class="string">'strong'</span><span class="keyword">);<br />
</span><span class="default">$italic </span><span class="keyword">= </span><span class="default">html</span><span class="keyword">(</span><span class="string">'i'</span><span class="keyword">);<br />
<br />
</span><span class="default">$msg</span><span class="keyword">= </span><span class="default">$div</span><span class="keyword">(</span><span class="default">$bold</span><span class="keyword">(</span><span class="default">$italic</span><span class="keyword">(</span><span class="string">"hello from the left sidebar"</span><span class="keyword">)));<br />
<br />
echo </span><span class="default">$container</span><span class="keyword">(<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$header</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"This is the header"</span><span class="keyword">).</span><span class="default">$pmain</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$lsidebar</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$msg</span><span class="keyword">).</span><span class="default">$rsidebar</span><span class="keyword">(<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="string">"This is the right sidebar"</span><span class="keyword">)).</span><span class="default">$footer</span><span class="keyword">(<br />
&nbsp;&nbsp;&nbsp; ));</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105901"></a>
 <div class="note">
  <strong class='user'>hernan_javier_saab at yahoo dot com</strong>
  <a href="#105901" class="date">23-Sep-2011 09:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When invoking an anonymous function through throughThroughFunction(), all variables are automatically binded to the inner function. In addition, variables created within the anonymous function are also "binded out" incorporating it to the scope where the anonymous function is being created.<br />
Enjoy!<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">ThroughThrogh </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$to_export </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$to_import </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// we eval() these functions in order to cleanup verbosity. Cleaner this way even though we all heard about the nightmares of eval()<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">const </span><span class="default">TX_START_X </span><span class="keyword">= </span><span class="string">'foreach(get_defined_vars() as $k=&gt;$v){$this-&gt;to_export[$k] =&amp;${$k};};'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TX_START_I </span><span class="keyword">= </span><span class="string">'foreach($that-&gt;to_export as $name =&gt; &amp;$variable) ${$name} = &amp;$variable;'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TX_END_X </span><span class="keyword">= </span><span class="string">'foreach(get_defined_vars() as $k=&gt;$v){if(!isset($that-&gt;to_export[$k])) $that-&gt;to_import[$k] =&amp;${$k};};'</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; const </span><span class="default">TX_END_I </span><span class="keyword">= </span><span class="string">'foreach($this-&gt;to_import as $name =&gt; &amp;$variable) ${$name} = &amp;$variable;'</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">hackClosure</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">, </span><span class="default">$inject_start</span><span class="keyword">, </span><span class="default">$inject_end</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$reflection </span><span class="keyword">= new </span><span class="default">ReflectionFunction</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$tmp </span><span class="keyword">= </span><span class="default">$reflection</span><span class="keyword">-&gt;</span><span class="default">getParameters</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$args </span><span class="keyword">= array(</span><span class="string">'$that'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$tmp </span><span class="keyword">as </span><span class="default">$a</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">array_push</span><span class="keyword">(</span><span class="default">$args</span><span class="keyword">, </span><span class="string">'$' </span><span class="keyword">. </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">getName</span><span class="keyword">() . (</span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">isDefaultValueAvailable</span><span class="keyword">() ? </span><span class="string">'=\'' </span><span class="keyword">. </span><span class="default">$a</span><span class="keyword">-&gt;</span><span class="default">getDefaultValue</span><span class="keyword">() . </span><span class="string">'\'' </span><span class="keyword">: </span><span class="string">''</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$file </span><span class="keyword">= new </span><span class="default">SplFileObject</span><span class="keyword">(</span><span class="default">$reflection</span><span class="keyword">-&gt;</span><span class="default">getFileName</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$file</span><span class="keyword">-&gt;</span><span class="default">seek</span><span class="keyword">(</span><span class="default">$reflection</span><span class="keyword">-&gt;</span><span class="default">getStartLine</span><span class="keyword">() - </span><span class="default">1</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$code </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while (</span><span class="default">$file</span><span class="keyword">-&gt;</span><span class="default">key</span><span class="keyword">() &lt; </span><span class="default">$reflection</span><span class="keyword">-&gt;</span><span class="default">getEndLine</span><span class="keyword">()) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$code </span><span class="keyword">.= </span><span class="default">$file</span><span class="keyword">-&gt;</span><span class="default">current</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$file</span><span class="keyword">-&gt;</span><span class="default">next</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$start </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">$code</span><span class="keyword">, </span><span class="string">'{'</span><span class="keyword">) + </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$end </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">$code</span><span class="keyword">, </span><span class="string">'}'</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">create_function</span><span class="keyword">(</span><span class="default">implode</span><span class="keyword">(</span><span class="string">', '</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">), </span><span class="string">"$inject_start;" </span><span class="keyword">.&nbsp; </span><span class="default">substr</span><span class="keyword">(</span><span class="default">$code</span><span class="keyword">, </span><span class="default">$start</span><span class="keyword">, </span><span class="default">$end </span><span class="keyword">- </span><span class="default">$start</span><span class="keyword">) . </span><span class="string">"$inject_end;"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//here is where we invoke the function<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">throughThroughFunction</span><span class="keyword">(</span><span class="default">$that</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">to_export</span><span class="keyword">[</span><span class="string">"that"</span><span class="keyword">] = </span><span class="default">$that</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$inject_start </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">TX_START_I</span><span class="keyword">;</span><span class="comment">// potentially you can add setup code in here<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$inject_end </span><span class="keyword">= </span><span class="default">self</span><span class="keyword">::</span><span class="default">TX_END_X</span><span class="keyword">;</span><span class="comment">// potentially you can add cleanup code in here <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$callback </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">hackClosure</span><span class="keyword">(</span><span class="default">$callback</span><span class="keyword">, </span><span class="default">$inject_start</span><span class="keyword">, </span><span class="default">$inject_end</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$callback</span><span class="keyword">, </span><span class="default">$that</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">//user test<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">test</span><span class="keyword">(){<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$outscope </span><span class="keyword">= </span><span class="string">"Source External Scope: test... not modified if seen at end of function\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= </span><span class="string">"Source External Scope: object attribute from outside anonymous function\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; eval(</span><span class="default">$this</span><span class="keyword">::</span><span class="default">TX_START_X</span><span class="keyword">); </span><span class="comment">//required to export all variables from scope<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">throughThroughFunction</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">, function(){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="string">"Source Anonymous Function\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="default">$outscope</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="default">$that</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$that</span><span class="keyword">-&gt;</span><span class="default">test </span><span class="keyword">= </span><span class="string">"Source Anonymous Function Scope: wow! Object attribute modified from inside anonymous function\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$outscope </span><span class="keyword">= </span><span class="string">"Source Anonymous Function Scope: yey! Variable Modified from the scope of an anonymous function!\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$from_anonymous_function </span><span class="keyword">= </span><span class="string">"Source Anonymous Function Scope: No way! How did you do that?\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } );<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; eval(</span><span class="default">$this</span><span class="keyword">::</span><span class="default">TX_END_I</span><span class="keyword">); </span><span class="comment">//require to bring newly created variables from anonymous function into current scope<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"\nSource External Scope\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$from_anonymous_function</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$outscope</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">$demo </span><span class="keyword">= new </span><span class="default">ThroughThrogh</span><span class="keyword">();<br />
</span><span class="default">$demo</span><span class="keyword">-&gt;</span><span class="default">test</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Output<br />
&nbsp;<br />
Source Anonymous Function<br />
Source External Scope: test... not modified if seen at end of function<br />
Source External Scope: object attribute from outside anonymous function<br />
<br />
Source External Scope<br />
Source Anonymous Function Scope: No way! How did you do that?<br />
Source Anonymous Function Scope: yey! Variable Modified from the scope of an anonymous function!<br />
Source Anonymous Function Scope: wow! Object attribute modified from inside anonymous function</span>
</code></div>
  </div>
 </div>
 <a name="105564"></a>
 <div class="note">
  <strong class='user'>reinaldorock at yahoo dot com dot br</strong>
  <a href="#105564" class="date">27-Aug-2011 08:15</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using closure to encapsulate environment<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">) use(&amp;</span><span class="default">$fib</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$n </span><span class="keyword">== </span><span class="default">0 </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">) + </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; };<br />
<br />
&nbsp;&nbsp; echo </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// 2<br />
&nbsp;&nbsp; </span><span class="default">$lie </span><span class="keyword">= </span><span class="default">$fib</span><span class="keyword">;<br />
&nbsp;&nbsp; </span><span class="default">$fib </span><span class="keyword">= function(){die(</span><span class="string">'error'</span><span class="keyword">);};</span><span class="comment">//rewrite $fib variable <br />
&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">$lie</span><span class="keyword">(</span><span class="default">5</span><span class="keyword">); </span><span class="comment">// error&nbsp;&nbsp; because $fib is referenced by closure<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Alternative Fibonacci implementation using a self called function like javascript to encapsulate references variables.<br />
<br />
<span class="default">&lt;?php<br />
$fib </span><span class="keyword">= </span><span class="default">call_user_func</span><span class="keyword">(function(){<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">) use(&amp;</span><span class="default">$fib</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$n </span><span class="keyword">== </span><span class="default">0 </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">) + </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; };<br />
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">;<br />
});<br />
<br />
echo </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">2</span><span class="keyword">) . </span><span class="string">"\n"</span><span class="keyword">;</span><span class="comment">//2<br />
</span><span class="default">$ok </span><span class="keyword">= </span><span class="default">$fib</span><span class="keyword">;<br />
<br />
</span><span class="default">$fib </span><span class="keyword">= function(){die(</span><span class="string">'error'</span><span class="keyword">)};</span><span class="comment">//rewrite $fib variable but don't referenced $fib used by closure<br />
</span><span class="keyword">echo </span><span class="default">$ok</span><span class="keyword">(</span><span class="default">5</span><span class="keyword">);</span><span class="comment">//result ok <br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="105385"></a>
 <div class="note">
  <strong class='user'>ldrut</strong>
  <a href="#105385" class="date">13-Aug-2011 01:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A common way to avoid contaminating Javascript global space with unneeded variables is to move the code into an immediately called anonymous closure.<br />
<br />
(function(){ ... })()<br />
<br />
The equivalent way to do that in PHP 5.3+ is<br />
<br />
call_user_func(function() use(closure-vars){ ... });</span>
</code></div>
  </div>
 </div>
 <a name="105261"></a>
 <div class="note">
  <strong class='user'>simon at generalflows dot com</strong>
  <a href="#105261" class="date">05-Aug-2011 08:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* An example showing how to use closures to implement a Python-like decorator <br />
&nbsp;* pattern.<br />
&nbsp;*<br />
&nbsp;* My goal was that you should be able to decorate a function with any<br />
&nbsp;* other function, then call the decorated function directly: <br />
&nbsp;*<br />
&nbsp;* Define function:&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $foo = function($a, $b, $c, ...) {...}<br />
&nbsp;* Define decorator:&nbsp; &nbsp; &nbsp; &nbsp; $decorator = function($func) {...}<br />
&nbsp;* Decorate it:&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $foo = $decorator($foo)<br />
&nbsp;* Call it:&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $foo($a, $b, $c, ...)<br />
&nbsp;*<br />
&nbsp;* This example show an authentication decorator for a service, using a simple<br />
&nbsp;* mock session and mock service. <br />
&nbsp;*/<br />
&nbsp;<br />
</span><span class="default">session_start</span><span class="keyword">();<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* Define an example decorator. A decorator function should take the form:<br />
&nbsp;* $decorator = function($func) {<br />
&nbsp;*&nbsp; &nbsp;&nbsp; return function() use $func) {<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // Do something, then call the decorated function when needed:<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; $args = func_get_args($func);<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; call_user_func_array($func, $args);<br />
&nbsp;*&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // Do something else.<br />
&nbsp;*&nbsp; &nbsp;&nbsp; };<br />
&nbsp;* };<br />
&nbsp;*/<br />
</span><span class="default">$authorise </span><span class="keyword">= function(</span><span class="default">$func</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return function() use (</span><span class="default">$func</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'is_authorised'</span><span class="keyword">] == </span><span class="default">true</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$args </span><span class="keyword">= </span><span class="default">func_get_args</span><span class="keyword">(</span><span class="default">$func</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$func</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Access Denied"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; };<br />
};<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* Define a function to be decorated, in this example a mock service that<br />
&nbsp;* need to be authorised. <br />
&nbsp;*/ <br />
</span><span class="default">$service </span><span class="keyword">= function(</span><span class="default">$foo</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"Service returns: $foo"</span><span class="keyword">;<br />
};<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* Decorate it. Ensure you replace the origin function reference with the<br />
&nbsp;* decorated function; ie just $authorise($service) won't work, so do<br />
&nbsp;* $service = $authorise($service)<br />
&nbsp;*/<br />
</span><span class="default">$service </span><span class="keyword">= </span><span class="default">$authorise</span><span class="keyword">(</span><span class="default">$service</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* Establish mock authorisation, call the service; should get <br />
&nbsp;* 'Service returns: test 1'. <br />
&nbsp;*/<br />
</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'is_authorised'</span><span class="keyword">] = </span><span class="default">true</span><span class="keyword">;<br />
</span><span class="default">$service</span><span class="keyword">(</span><span class="string">'test 1'</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* <br />
&nbsp;* Remove mock authorisation, call the service; should get 'Access Denied'. <br />
&nbsp;*/<br />
</span><span class="default">$_SESSION</span><span class="keyword">[</span><span class="string">'is_authorised'</span><span class="keyword">] = </span><span class="default">false</span><span class="keyword">;<br />
</span><span class="default">$service</span><span class="keyword">(</span><span class="string">'test 2'</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="102785"></a>
 <div class="note">
  <strong class='user'>paul at somewhere dot com</strong>
  <a href="#102785" class="date">06-Mar-2011 02:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I benched instantiating a function and lambda function.<br />
<br />
Functions used:<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">data</span><span class="keyword">() {<br />
&nbsp;</span><span class="default">$var </span><span class="keyword">= </span><span class="string">'hi'</span><span class="keyword">;<br />
}<br />
</span><span class="default">$lambda </span><span class="keyword">= function() {<br />
&nbsp;</span><span class="default">$var </span><span class="keyword">= </span><span class="string">'hi'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Bench for instantiating a function:<br />
1.692800000000000082422957 μs<br />
Bench for instantiating Lambda Function:<br />
0.906000000000000027533531 μs<br />
Bench for calling function:<br />
0.7153000000000000468958206 μs<br />
Bench for calling lambda function:<br />
0.6914000000000000145661261 μs<br />
<br />
Calling the lambda function and regular function fluctuates between .81 and .65 so they seem to be the same.</span>
</code></div>
  </div>
 </div>
 <a name="102615"></a>
 <div class="note">
  <strong class='user'>fabiolimasouto at gmail dot com</strong>
  <a href="#102615" class="date">24-Feb-2011 07:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You may have been disapointed if you tried to call a closure stored in an instance variable as you would regularly do with methods:<br />
<br />
<span class="default">&lt;?php<br />
<br />
$obj </span><span class="keyword">= new </span><span class="default">StdClass</span><span class="keyword">();<br />
<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">func </span><span class="keyword">= function(){<br />
&nbsp;echo </span><span class="string">"hello"</span><span class="keyword">;<br />
};<br />
<br />
</span><span class="comment">//$obj-&gt;func(); // doesn't work! php tries to match an instance method called "func" that is not defined in the original class' signature<br />
<br />
// you have to do this instead:<br />
</span><span class="default">$func </span><span class="keyword">= </span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">func</span><span class="keyword">;<br />
</span><span class="default">$func</span><span class="keyword">();<br />
<br />
</span><span class="comment">// or:<br />
</span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">func</span><span class="keyword">);<br />
<br />
</span><span class="comment">// however, you might wanna check this out:<br />
</span><span class="default">$array</span><span class="keyword">[</span><span class="string">'func'</span><span class="keyword">] = function(){<br />
&nbsp;echo </span><span class="string">"hello"</span><span class="keyword">;<br />
};<br />
<br />
</span><span class="default">$array</span><span class="keyword">[</span><span class="string">'func'</span><span class="keyword">](); </span><span class="comment">// it works! i discovered that just recently ;)<br />
</span><span class="default">?&gt;<br />
</span><br />
Now, coming back to the problem of assigning functions/methods "on the fly" to an object and being able to call them as if they were regular methods, you could trick php with this lawbreaker-code:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">test</span><span class="keyword">{<br />
&nbsp;private </span><span class="default">$functions </span><span class="keyword">= array();<br />
&nbsp;private </span><span class="default">$vars </span><span class="keyword">= array();<br />
&nbsp;<br />
&nbsp;function </span><span class="default">__set</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">,</span><span class="default">$data</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp; if(</span><span class="default">is_callable</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">))<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">functions</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp; else<br />
&nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">vars</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">$data</span><span class="keyword">;<br />
&nbsp;}<br />
&nbsp;<br />
&nbsp;function </span><span class="default">__get</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp; if(isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">vars</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">]))<br />
&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">vars</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">];<br />
&nbsp;}<br />
&nbsp;<br />
&nbsp;function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">,</span><span class="default">$args</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp; if(isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">functions</span><span class="keyword">[</span><span class="default">$method</span><span class="keyword">]))<br />
&nbsp; {<br />
&nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">functions</span><span class="keyword">[</span><span class="default">$method</span><span class="keyword">],</span><span class="default">$args</span><span class="keyword">);<br />
&nbsp; } else {<br />
&nbsp;&nbsp; </span><span class="comment">// error out<br />
&nbsp; </span><span class="keyword">}<br />
&nbsp;}<br />
}<br />
<br />
</span><span class="comment">// LET'S BREAK SOME LAW NOW!<br />
</span><span class="default">$obj </span><span class="keyword">= new </span><span class="default">test</span><span class="keyword">;<br />
<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">sayHelloWithMyName </span><span class="keyword">= function(</span><span class="default">$name</span><span class="keyword">){<br />
&nbsp;echo </span><span class="string">"Hello $name!"</span><span class="keyword">;<br />
};<br />
<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">sayHelloWithMyName</span><span class="keyword">(</span><span class="string">'Fabio'</span><span class="keyword">); </span><span class="comment">// Hello Fabio!<br />
<br />
// THE OLD WAY (NON-CLOSURE) ALSO WORKS:<br />
<br />
</span><span class="keyword">function </span><span class="default">sayHello</span><span class="keyword">()<br />
{<br />
&nbsp;echo </span><span class="string">"Hello!"</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">justSayHello </span><span class="keyword">= </span><span class="string">'sayHello'</span><span class="keyword">;<br />
</span><span class="default">$obj</span><span class="keyword">-&gt;</span><span class="default">justSayHello</span><span class="keyword">(); </span><span class="comment">// Hello!<br />
</span><span class="default">?&gt;<br />
</span><br />
NOTICE: of course this is very bad practice since you cannot refere to protected or private fields/methods inside these pseudo "methods" as they are not instance methods at all but rather ordinary functions/closures assigned to the object's instance variables "on the fly". But I hope you've enjoyed the jurney ;)</span>
</code></div>
  </div>
 </div>
 <a name="102445"></a>
 <div class="note">
  <strong class='user'>Suman Madavapeddi.</strong>
  <a href="#102445" class="date">14-Feb-2011 05:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
These&nbsp; Closures are really interesting to me .This would be helpful.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">Class </span><span class="default">Operations</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">ops</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">,</span><span class="default">$y</span><span class="keyword">,</span><span class="default">$op</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; switch (</span><span class="default">$op</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="string">'ADD'</span><span class="keyword">:return function() use(</span><span class="default">$x</span><span class="keyword">,</span><span class="default">$y</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$x</span><span class="keyword">+</span><span class="default">$y</span><span class="keyword">.</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="string">'SUB'</span><span class="keyword">: return function() use (</span><span class="default">$x</span><span class="keyword">,</span><span class="default">$y</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$x</span><span class="keyword">-</span><span class="default">$y</span><span class="keyword">.</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; default:&nbsp; &nbsp; return function(){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"Operation is not supported by class"</span><span class="keyword">.</span><span class="string">"&lt;br&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$op </span><span class="keyword">=&nbsp; new </span><span class="default">Operations</span><span class="keyword">();<br />
</span><span class="default">$fn1 </span><span class="keyword">= </span><span class="default">$op</span><span class="keyword">-&gt;</span><span class="default">ops</span><span class="keyword">(</span><span class="default">6</span><span class="keyword">,</span><span class="default">7</span><span class="keyword">,</span><span class="string">'ADD'</span><span class="keyword">);<br />
echo </span><span class="default">$fn1</span><span class="keyword">();<br />
<br />
</span><span class="default">$fn2 </span><span class="keyword">= </span><span class="default">$op</span><span class="keyword">-&gt;</span><span class="default">ops</span><span class="keyword">(</span><span class="default">6</span><span class="keyword">,</span><span class="default">2</span><span class="keyword">,</span><span class="string">'SUB'</span><span class="keyword">);<br />
echo </span><span class="default">$fn2</span><span class="keyword">();<br />
<br />
</span><span class="default">$fn2 </span><span class="keyword">= </span><span class="default">$op</span><span class="keyword">-&gt;</span><span class="default">ops</span><span class="keyword">(</span><span class="default">6</span><span class="keyword">,</span><span class="default">7</span><span class="keyword">,</span><span class="string">'MUL'</span><span class="keyword">);<br />
echo </span><span class="default">$fn2</span><span class="keyword">();<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="100649"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#100649" class="date">28-Oct-2010 12:54</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
use() parameters are early binding - they use the variable's value at the point where the lambda function is declared, rather than the point where the lambda function is called (late binding).<br />
<br />
If you want late binding put &amp; before the variable inside use()<br />
<span class="default">&lt;?php<br />
$fn </span><span class="keyword">= function () use (&amp;</span><span class="default">$var</span><span class="keyword">) { echo </span><span class="default">$var</span><span class="keyword">; };<br />
</span><span class="default">?&gt;<br />
</span>Examples:<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// problem 1: this should echo "Canada", not a php notice<br />
</span><span class="default">$fn </span><span class="keyword">= function () use (</span><span class="default">$country</span><span class="keyword">) { echo </span><span class="default">$country </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; };<br />
</span><span class="default">$country </span><span class="keyword">= </span><span class="string">'Canada'</span><span class="keyword">;<br />
</span><span class="default">$fn</span><span class="keyword">();<br />
<br />
</span><span class="comment">// problem 2: this should echo "Canada", not "UnitedStates"<br />
</span><span class="default">$country </span><span class="keyword">= </span><span class="string">'UnitedStates'</span><span class="keyword">;<br />
</span><span class="default">$fn </span><span class="keyword">= function () use (</span><span class="default">$country</span><span class="keyword">) { echo </span><span class="default">$country </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; };<br />
</span><span class="default">$country </span><span class="keyword">= </span><span class="string">'Canada'</span><span class="keyword">;<br />
</span><span class="default">$fn</span><span class="keyword">();<br />
<br />
</span><span class="comment">// problem 3: this should echo "Canada", not "UnitedStates"<br />
</span><span class="default">$country </span><span class="keyword">= (object)array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'UnitedStates'</span><span class="keyword">);<br />
</span><span class="default">$fn </span><span class="keyword">= function () use (</span><span class="default">$country</span><span class="keyword">) { echo </span><span class="default">$country</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; };<br />
</span><span class="default">$country </span><span class="keyword">= (object)array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'Canada'</span><span class="keyword">);<br />
</span><span class="default">$fn</span><span class="keyword">();<br />
<br />
</span><span class="comment">// problem 4: this outputs "Canada". if this outputs "Canada",<br />
// then so should problem 2 above. otherwise this should be<br />
// just as broken as problem 2 and be outputting "UnitedStates"<br />
</span><span class="default">$country </span><span class="keyword">= (object)array(</span><span class="string">'name' </span><span class="keyword">=&gt; </span><span class="string">'UnitedStates'</span><span class="keyword">);<br />
</span><span class="default">$fn </span><span class="keyword">= function () use (</span><span class="default">$country</span><span class="keyword">) { echo </span><span class="default">$country</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">; };<br />
</span><span class="default">$country</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="string">'Canada'</span><span class="keyword">;<br />
</span><span class="default">$fn</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span>see <a href="http://bugs.php.net/bug.php?id=50980" rel="nofollow" target="_blank">http://bugs.php.net/bug.php?id=50980</a><br />
(I've just quoted from there, but if you want you can read there the whole feature request)</span>
</code></div>
  </div>
 </div>
 <a name="100545"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#100545" class="date">22-Oct-2010 07:00</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As an alternative to gabriel's recursive construction, you may instead assign the recursive function to a variable, and use it by reference, thus:<br />
<br />
<span class="default">&lt;?php<br />
$fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">)use(&amp;</span><span class="default">$fib</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$n </span><span class="keyword">== </span><span class="default">0 </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">) + </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
};<br />
<br />
echo </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">10</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span>Hardly a sensible implementation of the Fibonacci sequence, but that's not the point! The point is that the variable needs to be used by reference, not value.<br />
<br />
Without the '&amp;', the anonymous function gets the value of $fib at the time the function is being created. But until the function has been created, $fib can't have it as a value! It's not until AFTER the function has been assigned to $fib that $fib can be used to call the function - but by then it's too late to pass its value to the function being created!<br />
<br />
Using a reference resolves the dilemma: when called, the anonymous function will use $fib's current value, which will be the anonymous function itself.<br />
<br />
At least, it will be if you don't reassign $fib to anything else between creating the function and calling it:<br />
<br />
<span class="default">&lt;?php<br />
$fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">)use(&amp;</span><span class="default">$fib</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$n </span><span class="keyword">== </span><span class="default">0 </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">) + </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
};<br />
<br />
</span><span class="default">$lie </span><span class="keyword">= </span><span class="default">$fib</span><span class="keyword">;<br />
<br />
</span><span class="default">$fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">100</span><span class="keyword">;<br />
};<br />
<br />
echo </span><span class="default">$lie</span><span class="keyword">(</span><span class="default">10</span><span class="keyword">); </span><span class="comment">// 200, because $fib(10 - 1) and $fib(10 - 2) both return 100.<br />
</span><span class="default">?&gt;<br />
</span><br />
Of course, that's true of any variable: if you don't want its value to change, don't change its value.<br />
<br />
All the usual scoping rules for variables still apply: a local variable in a function is a different variable from another one with the same name in another function:<br />
<br />
<span class="default">&lt;?php<br />
$fib </span><span class="keyword">= function(</span><span class="default">$n</span><span class="keyword">)use(&amp;</span><span class="default">$fib</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$n </span><span class="keyword">== </span><span class="default">0 </span><span class="keyword">|| </span><span class="default">$n </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) return </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">) + </span><span class="default">$fib</span><span class="keyword">(</span><span class="default">$n </span><span class="keyword">- </span><span class="default">2</span><span class="keyword">);<br />
};<br />
<br />
</span><span class="default">$bark </span><span class="keyword">= function(</span><span class="default">$f</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$fib </span><span class="keyword">= </span><span class="string">'cake'</span><span class="keyword">;&nbsp; &nbsp; </span><span class="comment">// A totally different variable from the $fib above.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">2 </span><span class="keyword">* </span><span class="default">$f</span><span class="keyword">(</span><span class="default">5</span><span class="keyword">);<br />
};<br />
<br />
echo </span><span class="default">$bark</span><span class="keyword">(</span><span class="default">$fib</span><span class="keyword">); </span><span class="comment">// 16, twice the fifth Fibonacci number<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99477"></a>
 <div class="note">
  <strong class='user'>anonymous</strong>
  <a href="#99477" class="date">19-Aug-2010 03:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Base dao class illustrating the usefulness of closures.<br />
* Handles opening and closing of connections.<br />
* Adds slashes sql<br />
* Type checking of sql parameters and casts as appropriate<br />
* Provides hook for processing of result set and emitting one or more objects.<br />
* Provides hook for accessing underlying link and result objects.<br />
<br />
<span class="default">&lt;?php<br />
<br />
define</span><span class="keyword">(</span><span class="string">"userName"</span><span class="keyword">,</span><span class="string">"root"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"password"</span><span class="keyword">,</span><span class="string">"root"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"dbName"</span><span class="keyword">,</span><span class="string">"ahcdb"</span><span class="keyword">);<br />
</span><span class="default">define</span><span class="keyword">(</span><span class="string">"hostName"</span><span class="keyword">,</span><span class="string">"localhost"</span><span class="keyword">);<br />
<br />
class </span><span class="default">BaseDao </span><span class="keyword">{<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">getConnection</span><span class="keyword">()&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$link </span><span class="keyword">= </span><span class="default">mysql_connect</span><span class="keyword">(</span><span class="default">hostName</span><span class="keyword">, </span><span class="default">userName</span><span class="keyword">, </span><span class="default">password</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!</span><span class="default">$link</span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">"Could not connect: " </span><span class="keyword">. </span><span class="default">mysql_error</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (!</span><span class="default">mysql_select_db</span><span class="keyword">(</span><span class="default">dbName</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">"Could not select database: " </span><span class="keyword">. </span><span class="default">mysql_error</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$link</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">setParams</span><span class="keyword">(&amp; </span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">)&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$params </span><span class="keyword">!= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$sql </span><span class="keyword">= </span><span class="default">vsprintf</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">array_map</span><span class="keyword">(function(</span><span class="default">$n</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_int</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return (int)</span><span class="default">$n</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_float</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return (float)</span><span class="default">$n</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"'"</span><span class="keyword">.</span><span class="default">mysql_real_escape_string</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">).</span><span class="string">"'"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">mysql_real_escape_string</span><span class="keyword">(</span><span class="default">$n</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }, </span><span class="default">$params</span><span class="keyword">));<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">executeQuery</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">, </span><span class="default">$callback </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">)&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$link&nbsp; </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">getConnection</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">setParams</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if((</span><span class="default">$result </span><span class="keyword">= </span><span class="default">mysql_query</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">)) != </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$callback </span><span class="keyword">!= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$return </span><span class="keyword">= </span><span class="default">$callback</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$link </span><span class="keyword">!= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">mysql_close</span><span class="keyword">(</span><span class="default">$link</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(!</span><span class="default">$result</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; die(</span><span class="string">"Fatal Error: Invalid query '$sql' : " </span><span class="keyword">. </span><span class="default">mysql_error</span><span class="keyword">());<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$return</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">getList</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">)&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">executeQuery</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">, function(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">) use (</span><span class="default">$callback</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$idx </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$list </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; while (</span><span class="default">$row </span><span class="keyword">= </span><span class="default">mysql_fetch_assoc</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">$callback </span><span class="keyword">!= </span><span class="default">null</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$list</span><span class="keyword">[</span><span class="default">$idx</span><span class="keyword">] = </span><span class="default">$callback</span><span class="keyword">(</span><span class="default">$idx</span><span class="keyword">++, </span><span class="default">$row</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$list</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">getSingle</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">)&nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">executeQuery</span><span class="keyword">(</span><span class="default">$sql</span><span class="keyword">, </span><span class="default">$params</span><span class="keyword">, function(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">) use (</span><span class="default">$callback</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$row </span><span class="keyword">= </span><span class="default">mysql_fetch_assoc</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$obj </span><span class="keyword">= </span><span class="default">$callback</span><span class="keyword">(</span><span class="default">$row</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$obj</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Example&nbsp; &nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; var </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">Example</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">, </span><span class="default">$name</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id </span><span class="keyword">= </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$name</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">setId</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">id </span><span class="keyword">= </span><span class="default">$id</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">ExampleDao </span><span class="keyword">extends </span><span class="default">BaseDao&nbsp; &nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">getAll</span><span class="keyword">(){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">parent</span><span class="keyword">::</span><span class="default">getList</span><span class="keyword">(</span><span class="string">"select * from nodes"</span><span class="keyword">, </span><span class="default">null</span><span class="keyword">, function(</span><span class="default">$idx</span><span class="keyword">, </span><span class="default">$row</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return new </span><span class="default">Example</span><span class="keyword">(</span><span class="default">$row</span><span class="keyword">[</span><span class="string">"id"</span><span class="keyword">], </span><span class="default">$row</span><span class="keyword">[</span><span class="string">"name"</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">load</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">parent</span><span class="keyword">::</span><span class="default">getSingle</span><span class="keyword">(</span><span class="string">"select * from nodes where id = %1\$s"</span><span class="keyword">, array(</span><span class="default">$id</span><span class="keyword">), function(</span><span class="default">$row</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return new </span><span class="default">Example</span><span class="keyword">(</span><span class="default">$row</span><span class="keyword">[</span><span class="string">"id"</span><span class="keyword">], </span><span class="default">$row</span><span class="keyword">[</span><span class="string">"name"</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">update</span><span class="keyword">(</span><span class="default">$example</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">parent</span><span class="keyword">::</span><span class="default">executeQuery</span><span class="keyword">(</span><span class="string">"update nodes set name = '' where&nbsp; id = -1"</span><span class="keyword">, </span><span class="default">null</span><span class="keyword">, function(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">insert</span><span class="keyword">(&amp; </span><span class="default">$example</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">parent</span><span class="keyword">::</span><span class="default">executeQuery</span><span class="keyword">(</span><span class="string">"insert into nodes"</span><span class="keyword">, </span><span class="default">null</span><span class="keyword">, function(</span><span class="default">$result</span><span class="keyword">, </span><span class="default">$link</span><span class="keyword">) use (</span><span class="default">$example</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$id </span><span class="keyword">= </span><span class="default">mysql_insert_id</span><span class="keyword">(</span><span class="default">$link</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$example</span><span class="keyword">-&gt;</span><span class="default">setId</span><span class="keyword">(</span><span class="default">$id</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; });<br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; <br />
}<br />
<br />
</span><span class="default">$exampleDao </span><span class="keyword">= new </span><span class="default">ExampleDao</span><span class="keyword">();<br />
<br />
</span><span class="default">$list </span><span class="keyword">= </span><span class="default">$exampleDao</span><span class="keyword">-&gt;</span><span class="default">getAll</span><span class="keyword">());<br />
<br />
</span><span class="default">$exampleObject </span><span class="keyword">= </span><span class="default">$exampleDao</span><span class="keyword">-&gt;</span><span class="default">load</span><span class="keyword">(</span><span class="default">1</span><span class="keyword">));<br />
<br />
</span><span class="default">$exampleDao</span><span class="keyword">-&gt;</span><span class="default">update</span><span class="keyword">(</span><span class="default">$exampleObject</span><span class="keyword">);<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="99287"></a>
 <div class="note">
  <strong class='user'>orls</strong>
  <a href="#99287" class="date">08-Aug-2010 06:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Watch out when 'importing' variables to a closure's scope&nbsp; -- it's easy to miss / forget that they are actually being *copied* into the closure's scope, rather than just being made available.<br />
<br />
So you will need to explicitly pass them in by reference if your closure cares about their contents over time:<br />
<br />
<span class="default">&lt;?php<br />
$result </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
<br />
</span><span class="default">$one </span><span class="keyword">= function()<br />
{ </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">); };<br />
<br />
</span><span class="default">$two </span><span class="keyword">= function() use (</span><span class="default">$result</span><span class="keyword">)<br />
{ </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">); };<br />
<br />
</span><span class="default">$three </span><span class="keyword">= function() use (&amp;</span><span class="default">$result</span><span class="keyword">)<br />
{ </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$result</span><span class="keyword">); };<br />
<br />
</span><span class="default">$result</span><span class="keyword">++;<br />
<br />
</span><span class="default">$one</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// outputs NULL: $result is not in scope<br />
</span><span class="default">$two</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// outputs int(0): $result was copied<br />
</span><span class="default">$three</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// outputs int(1)<br />
</span><span class="default">?&gt;<br />
</span><br />
Another less trivial example with objects (what I actually tripped up on):<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">//set up variable in advance<br />
</span><span class="default">$myInstance </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">;<br />
<br />
</span><span class="default">$broken </span><span class="keyword">= function() </span><span class="default">uses </span><span class="keyword">(</span><span class="default">$myInstance</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(!empty(</span><span class="default">$myInstance</span><span class="keyword">)) </span><span class="default">$myInstance</span><span class="keyword">-&gt;</span><span class="default">doSomething</span><span class="keyword">();<br />
};<br />
<br />
</span><span class="default">$working </span><span class="keyword">= function() </span><span class="default">uses </span><span class="keyword">(&amp;</span><span class="default">$myInstance</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; if(!empty(</span><span class="default">$myInstance</span><span class="keyword">)) </span><span class="default">$myInstance</span><span class="keyword">-&gt;</span><span class="default">doSomething</span><span class="keyword">();<br />
}<br />
<br />
</span><span class="comment">//$myInstance might be instantiated, might not be<br />
</span><span class="keyword">if(</span><span class="default">SomeBusinessLogic</span><span class="keyword">::</span><span class="default">worked</span><span class="keyword">() == </span><span class="default">true</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$myInstance </span><span class="keyword">= new </span><span class="default">myClass</span><span class="keyword">();<br />
}<br />
<br />
</span><span class="default">$broken</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// will never do anything: $myInstance will ALWAYS be null inside this closure.<br />
</span><span class="default">$working</span><span class="keyword">();&nbsp; &nbsp; </span><span class="comment">// will call doSomething if $myInstance is instantiated<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="98978"></a>
 <div class="note">
  <strong class='user'>gabriel dot totoliciu at ddsec dot net</strong>
  <a href="#98978" class="date">19-Jul-2010 10:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to make a recursive closure, you will need to write this:<br />
<br />
$some_var1="1";<br />
$some_var2="2";<br />
<br />
function($param1, $param2) use ($some_var1, $some_var2)<br />
{<br />
<br />
//some code here<br />
<br />
call_user_func(__FUNCTION__, $other_param1, $other_param2);<br />
<br />
//some code here<br />
<br />
}<br />
<br />
If you need to pass values by reference you should check out<br />
<br />
<a href="http://www.php.net/manual/en/function.call-user-func.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/function.call-user-func.php</a><br />
<a href="http://www.php.net/manual/en/function.call-user-func-array.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/function.call-user-func-array.php</a><br />
<br />
If you're wondering if $some_var1 and $some_var2 are still visible by using the call_user_func, yes, they are available.</span>
</code></div>
  </div>
 </div>
 <a name="98384"></a>
 <div class="note">
  <strong class='user'>martin dot partel at gmail dot com</strong>
  <a href="#98384" class="date">11-Jun-2010 10:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
$this is currently (PHP 5.3.2) not usable directly with closures.<br />
<br />
One can write:<br />
<span class="default">&lt;?php<br />
$self </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">;<br />
function () use (</span><span class="default">$self</span><span class="keyword">) { ... }<br />
</span><span class="default">?&gt;<br />
</span>but then the private/protected members of $this cannot be used inside the closure. This makes closures much less useful in OO code.<br />
<br />
Until this is fixed, one can cheat using reflection:<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">FullAccessWrapper<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$_self</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; protected </span><span class="default">$_refl</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$self</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_self </span><span class="keyword">= </span><span class="default">$self</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_refl </span><span class="keyword">= new </span><span class="default">ReflectionObject</span><span class="keyword">(</span><span class="default">$self</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$mrefl </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_refl</span><span class="keyword">-&gt;</span><span class="default">getMethod</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$mrefl</span><span class="keyword">-&gt;</span><span class="default">setAccessible</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$mrefl</span><span class="keyword">-&gt;</span><span class="default">invokeArgs</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_self</span><span class="keyword">, </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__set</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prefl </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_refl</span><span class="keyword">-&gt;</span><span class="default">getProperty</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prefl</span><span class="keyword">-&gt;</span><span class="default">setAccessible</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prefl</span><span class="keyword">-&gt;</span><span class="default">setValue</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_self</span><span class="keyword">, </span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__get</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prefl </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_refl</span><span class="keyword">-&gt;</span><span class="default">getProperty</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prefl</span><span class="keyword">-&gt;</span><span class="default">setAccessible</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$prefl</span><span class="keyword">-&gt;</span><span class="default">getValue</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">_self</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">__isset</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$value </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">__get</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return isset(</span><span class="default">$value</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">/**<br />
&nbsp;* Usage:<br />
&nbsp;* $self = giveAccess($this);<br />
&nbsp;* function() use ($self) { $self-&gt;privateMember... }<br />
&nbsp;*/<br />
</span><span class="keyword">function </span><span class="default">giveAccess</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; return new </span><span class="default">FullAccessWrapper</span><span class="keyword">(</span><span class="default">$obj</span><span class="keyword">);<br />
}<br />
<br />
</span><span class="comment">// Example:<br />
<br />
</span><span class="keyword">class </span><span class="default">Foo<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; private </span><span class="default">$x </span><span class="keyword">= </span><span class="default">3</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">f</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">15</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">getClosureUsingPrivates</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$self </span><span class="keyword">= </span><span class="default">giveAccess</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return function () use (</span><span class="default">$self</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$self</span><span class="keyword">-&gt;</span><span class="default">x </span><span class="keyword">* </span><span class="default">$self</span><span class="keyword">-&gt;</span><span class="default">f</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
</span><span class="default">$closure </span><span class="keyword">= </span><span class="default">$foo</span><span class="keyword">-&gt;</span><span class="default">getClosureUsingPrivates</span><span class="keyword">();<br />
echo </span><span class="default">$closure</span><span class="keyword">() . </span><span class="string">"\n"</span><span class="keyword">; </span><span class="comment">// Prints 45 as expected<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97906"></a>
 <div class="note">
  <strong class='user'>kdelux at gmail dot com</strong>
  <a href="#97906" class="date">14-May-2010 08:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is an example of one way to define, then use the variable ( $this ) in Closure functions.&nbsp; The code below explores all uses, and shows restrictions.<br />
<br />
The most useful tool in this snippet is the requesting_class() function that will tell you which class is responsible for executing the current Closure().&nbsp; <br />
<br />
Overview:<br />
-----------------------<br />
Successfully find calling object reference.<br />
Successfully call $this(__invoke);<br />
Successfully reference $$this-&gt;name;<br />
Successfully call call_user_func(array($this, 'method'))<br />
<br />
Failure: reference anything through $this-&gt;<br />
Failure: $this-&gt;name = ''; <br />
Failure: $this-&gt;delfect(); <br />
<br />
<span class="default">&lt;?php<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">requesting_class</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">debug_backtrace</span><span class="keyword">(</span><span class="default">true</span><span class="keyword">) as </span><span class="default">$stack</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(isset(</span><span class="default">$stack</span><span class="keyword">[</span><span class="string">'object'</span><span class="keyword">])){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$stack</span><span class="keyword">[</span><span class="string">'object'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; class </span><span class="default">Person<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public </span><span class="default">$name </span><span class="keyword">= </span><span class="string">''</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public </span><span class="default">$head </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public </span><span class="default">$feet </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public </span><span class="default">$deflected </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">__invoke</span><span class="keyword">(</span><span class="default">$p</span><span class="keyword">){ return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">$p</span><span class="keyword">; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">__toString</span><span class="keyword">(){ return </span><span class="string">'this'</span><span class="keyword">; } </span><span class="comment">// test for reference<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">__construct</span><span class="keyword">(</span><span class="default">$name</span><span class="keyword">){ </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name </span><span class="keyword">= </span><span class="default">$name</span><span class="keyword">; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; function </span><span class="default">deflect</span><span class="keyword">(){ </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">deflected </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">shoot</span><span class="keyword">()<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; { </span><span class="comment">// If customAttack is defined, use that as the shoot resut.&nbsp; Otherwise shoot feet<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">is_callable</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">customAttack</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">customAttack</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">feet </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$p </span><span class="keyword">= new </span><span class="default">Person</span><span class="keyword">(</span><span class="string">'Bob'</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$p</span><span class="keyword">-&gt;</span><span class="default">customAttack </span><span class="keyword">= <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; function(){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$this</span><span class="keyword">; </span><span class="comment">// Notice: Undefined variable: this<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; #$this = new Class() // FATAL ERROR<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; // Trick to assign the variable '$this'<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">extract</span><span class="keyword">(array(</span><span class="string">'this' </span><span class="keyword">=&gt; </span><span class="default">requesting_class</span><span class="keyword">())); </span><span class="comment">// Determine what class is responsible for making the call to Closure<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">( </span><span class="default">$this&nbsp; </span><span class="keyword">);&nbsp; </span><span class="comment">// Passive reference works<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">( $</span><span class="default">$this </span><span class="keyword">); </span><span class="comment">// Added to class:&nbsp; function __toString(){ return 'this'; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$name </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">(</span><span class="string">'name'</span><span class="keyword">); </span><span class="comment">// Success<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">$name</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// Outputs: Bob<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">'&lt;br /&gt;'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo $</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">name</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">call_user_func_array</span><span class="keyword">(array(</span><span class="default">$this</span><span class="keyword">, </span><span class="string">'deflect'</span><span class="keyword">), array()); </span><span class="comment">// SUCCESSFULLY CALLED<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; #$this-&gt;head = 0; //** FATAL ERROR: Using $this when not in object context<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">$</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">head </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;&nbsp; </span><span class="comment">// Successfully sets value<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">};<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$p</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$p</span><span class="keyword">-&gt;</span><span class="default">shoot</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">print_r</span><span class="keyword">(</span><span class="default">$p</span><span class="keyword">);<br />
<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; die();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="97296"></a>
 <div class="note">
  <strong class='user'>Hayley Watson</strong>
  <a href="#97296" class="date">12-Apr-2010 06:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the code<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">new_counter</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$counter </span><span class="keyword">= </span><span class="default">mt_rand</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; return function()use(&amp;</span><span class="default">$counter</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return ++</span><span class="default">$counter</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; };<br />
}<br />
<br />
</span><span class="default">$t1 </span><span class="keyword">= </span><span class="default">new_counter</span><span class="keyword">();<br />
</span><span class="default">$t2 </span><span class="keyword">= </span><span class="default">new_counter</span><span class="keyword">();<br />
<br />
echo </span><span class="default">$t1</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$t1</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$t2</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$t2</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$t1</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
echo </span><span class="default">$t1</span><span class="keyword">(),</span><span class="string">"\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
The variable $counter is local to new_counter() and is used by reference by the returned lambda function.<br />
<br />
Because $counter is not static, a new variable is created each time new_counter() is called.<br />
<br />
But because the lambda function uses a REFERENCE to the $counter variable - and not a new local variable with a copy of the value $counter had when the lambda function was constructed - the $counter variable created when new_counter() ran still exists (because a reference to it still exists).<br />
<br />
Every lambda function has a variable reference "hardwired" into it. That variable therefore persists across calls to the lambda function - rather like a static variable.<br />
<br />
But that variable is only LOCAL to the new_counter() function that created it. As soon as new_counter() returns, it gives up its reference to $counter. When new_counter() is called again, it gets allocated a NEW $counter variable and gives a reference to THAT variable to the lambda function it constructs.<br />
<br />
So the lambda functions in $t1 and $t2 each have their OWN $counter variable - separate from the other's, which persists from one call to the next.<br />
<br />
The effect is very similar to declaring and initialising $counter as static within the lambda function - and then it doesn't need to use anything from new_counter() - but you can't initialise a static variable with a function call like mt_rand()!<br />
<br />
Pending a decision on what it should mean, $this is not currently (as of 5.3.2) usable within anonymous functions.<br />
<br />
There are roughly two positions (plus attempts at compromise), that can be called "early" and "late".<br />
<br />
Early: $this refers to the object in whose scope the anonymous function is constructed.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Creator<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">make_anonymous</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; return function()<br />
&nbsp;&nbsp; &nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; };<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Caller<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$p</span><span class="keyword">;<br />
&nbsp;&nbsp; public function </span><span class="default">call_anonymous</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">p </span><span class="keyword">= </span><span class="default">$f</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$alpha </span><span class="keyword">= new </span><span class="default">Creator</span><span class="keyword">;<br />
</span><span class="default">$omega </span><span class="keyword">= new </span><span class="default">Caller</span><span class="keyword">;<br />
</span><span class="default">$omega</span><span class="keyword">-&gt;</span><span class="default">call_anonymous</span><span class="keyword">(</span><span class="default">$alpha</span><span class="keyword">-&gt;</span><span class="default">create_anonymous</span><span class="keyword">());<br />
</span><span class="comment">// $omega-&gt;p === $alpha<br />
</span><span class="default">?&gt;<br />
</span><br />
Late: $this refers to the object in whose scope the anonymous function is called.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">class </span><span class="default">Creator<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public function </span><span class="default">make_anonymous</span><span class="keyword">()<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; return function()<br />
&nbsp;&nbsp; &nbsp; &nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; return </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; };<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Caller<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; public </span><span class="default">$p</span><span class="keyword">;<br />
&nbsp;&nbsp; public function </span><span class="default">call_anonymous</span><span class="keyword">(</span><span class="default">$f</span><span class="keyword">)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">p </span><span class="keyword">= </span><span class="default">$f</span><span class="keyword">();<br />
&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="default">$alpha </span><span class="keyword">= new </span><span class="default">Creator</span><span class="keyword">;<br />
</span><span class="default">$omega </span><span class="keyword">= new </span><span class="default">Caller</span><span class="keyword">;<br />
</span><span class="default">$omega</span><span class="keyword">-&gt;</span><span class="default">call_anonymous</span><span class="keyword">(</span><span class="default">$alpha</span><span class="keyword">-&gt;</span><span class="default">create_anonymous</span><span class="keyword">());<br />
</span><span class="comment">// $omega-&gt;p === $omega<br />
</span><span class="default">?&gt;<br />
</span><br />
So until this is cleared up, $this won't work in an anonymous function. (Personally, I favour the early method; otherwise the anonymous function - and therefore its Creator - has access to all of the Caller's private properties.)</span>
</code></div>
  </div>
 </div>
 <a name="96755"></a>
 <div class="note">
  <strong class='user'>housni dot yakoob at gmail dot com</strong>
  <a href="#96755" class="date">14-Mar-2010 09:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to make sure that one of the parameters of your function is a Closure, you can use Type Hinting.<br />
see: <a href="http://php.net/manual/en/language.oop5.typehinting.php" rel="nofollow" target="_blank">http://php.net/manual/en/language.oop5.typehinting.php</a><br />
<br />
Example:<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">TheRoot<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">poidh</span><span class="keyword">(</span><span class="default">$param</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"TheRoot $param!"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }&nbsp;&nbsp; <br />
<br />
}<br />
<br />
class </span><span class="default">Internet<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># here, $my_closure must be of type object Closure<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">public function </span><span class="default">run_my_closure</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">, </span><span class="default">Closure $my_closure</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$my_closure</span><span class="keyword">(</span><span class="default">$bar</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; }&nbsp;&nbsp; <br />
}<br />
<br />
</span><span class="default">$Internet </span><span class="keyword">= new </span><span class="default">Internet</span><span class="keyword">();<br />
</span><span class="default">$Root </span><span class="keyword">= new </span><span class="default">TheRoot</span><span class="keyword">();<br />
<br />
</span><span class="default">$Internet</span><span class="keyword">-&gt;</span><span class="default">run_my_closure</span><span class="keyword">(</span><span class="default">$Root</span><span class="keyword">, function(</span><span class="default">$Object</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$Object</span><span class="keyword">-&gt;</span><span class="default">poidh</span><span class="keyword">(</span><span class="default">42</span><span class="keyword">);<br />
});<br />
<br />
</span><span class="default">?&gt;<br />
</span>The above code simply yields:<br />
"TheRoot 42!"<br />
<br />
NOTE: If you are using namespaces, make sure you give a fully qualified namespace.<br />
<br />
print_r() of Internet::run_my_closure's $my_closure<br />
<span class="default">&lt;?php<br />
Closure Object<br />
</span><span class="keyword">(<br />
&nbsp;&nbsp;&nbsp; [</span><span class="default">parameter</span><span class="keyword">] =&gt; Array<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; (<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; [</span><span class="default">$Object</span><span class="keyword">] =&gt; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; )<br />
<br />
)<br />
</span><span class="default">?&gt;<br />
</span><br />
var_dump() of Internet::run_my_closure's $my_closure<br />
<span class="default">&lt;?php<br />
object</span><span class="keyword">(</span><span class="default">Closure</span><span class="keyword">)</span><span class="comment">#3 (1) {<br />
&nbsp; </span><span class="keyword">[</span><span class="string">"parameter"</span><span class="keyword">]=&gt;<br />
&nbsp; array(</span><span class="default">1</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; [</span><span class="string">"$Object"</span><span class="keyword">]=&gt;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">string</span><span class="keyword">(</span><span class="default">10</span><span class="keyword">) </span><span class="string">""<br />
&nbsp; </span><span class="keyword">}<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96579"></a>
 <div class="note">
  <strong class='user'>aaron at afloorabove dot com</strong>
  <a href="#96579" class="date">05-Mar-2010 02:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Anonymous functions are great for events!<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Event </span><span class="keyword">{<br />
<br />
&nbsp; public static </span><span class="default">$events </span><span class="keyword">= array();<br />
&nbsp; <br />
&nbsp; public static function </span><span class="default">bind</span><span class="keyword">(</span><span class="default">$event</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">, </span><span class="default">$obj </span><span class="keyword">= </span><span class="default">null</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if (!</span><span class="default">self</span><span class="keyword">::</span><span class="default">$events</span><span class="keyword">[</span><span class="default">$event</span><span class="keyword">]) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$events</span><span class="keyword">[</span><span class="default">$event</span><span class="keyword">] = array();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">self</span><span class="keyword">::</span><span class="default">$events</span><span class="keyword">[</span><span class="default">$event</span><span class="keyword">][] = (</span><span class="default">$obj </span><span class="keyword">=== </span><span class="default">null</span><span class="keyword">)&nbsp; ? </span><span class="default">$callback </span><span class="keyword">: array(</span><span class="default">$obj</span><span class="keyword">, </span><span class="default">$callback</span><span class="keyword">);<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; public static function </span><span class="default">run</span><span class="keyword">(</span><span class="default">$event</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if (!</span><span class="default">self</span><span class="keyword">::</span><span class="default">$events</span><span class="keyword">[</span><span class="default">$event</span><span class="keyword">]) return;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">self</span><span class="keyword">::</span><span class="default">$events</span><span class="keyword">[</span><span class="default">$event</span><span class="keyword">] as </span><span class="default">$callback</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (</span><span class="default">call_user_func</span><span class="keyword">(</span><span class="default">$callback</span><span class="keyword">) === </span><span class="default">false</span><span class="keyword">) break;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
<br />
}<br />
<br />
function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp; echo </span><span class="string">"Hello from function hello()\n"</span><span class="keyword">;<br />
}<br />
<br />
class </span><span class="default">Foo </span><span class="keyword">{<br />
&nbsp; function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"Hello from foo-&gt;hello()\n"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
class </span><span class="default">Bar </span><span class="keyword">{<br />
&nbsp; function </span><span class="default">hello</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"Hello from Bar::hello()\n"</span><span class="keyword">;<br />
&nbsp; }<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= new </span><span class="default">Foo</span><span class="keyword">();<br />
<br />
</span><span class="comment">// bind a global function to the 'test' event<br />
</span><span class="default">Event</span><span class="keyword">::</span><span class="default">bind</span><span class="keyword">(</span><span class="string">"test"</span><span class="keyword">, </span><span class="string">"hello"</span><span class="keyword">);<br />
<br />
</span><span class="comment">// bind an anonymous function<br />
</span><span class="default">Event</span><span class="keyword">::</span><span class="default">bind</span><span class="keyword">(</span><span class="string">"test"</span><span class="keyword">, function() { echo </span><span class="string">"Hello from anonymous function\n"</span><span class="keyword">; });<br />
<br />
</span><span class="comment">// bind an class function on an instance<br />
</span><span class="default">Event</span><span class="keyword">::</span><span class="default">bind</span><span class="keyword">(</span><span class="string">"test"</span><span class="keyword">, </span><span class="string">"hello"</span><span class="keyword">, </span><span class="default">$foo</span><span class="keyword">);<br />
<br />
</span><span class="comment">// bind a static class function<br />
</span><span class="default">Event</span><span class="keyword">::</span><span class="default">bind</span><span class="keyword">(</span><span class="string">"test"</span><span class="keyword">, </span><span class="string">"Bar::hello"</span><span class="keyword">);<br />
<br />
</span><span class="default">Event</span><span class="keyword">::</span><span class="default">run</span><span class="keyword">(</span><span class="string">"test"</span><span class="keyword">);<br />
<br />
</span><span class="comment">/* Output<br />
Hello from function hello()<br />
Hello from anonymous function<br />
Hello from foo-&gt;hello()<br />
Hello from Bar::hello()<br />
*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96341"></a>
 <div class="note">
  <strong class='user'>ljackson at jjcons dot com</strong>
  <a href="#96341" class="date">21-Feb-2010 10:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
appears kwilson at shuttlebox dot net that you may have just made unintended side effect. Note that adding the global $variable to your test function make the closure function echo second rather than first So the anonymous function works as expected with respect to globals.<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $variable </span><span class="keyword">= </span><span class="string">"first"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$closure </span><span class="keyword">= function() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global </span><span class="default">$variable</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$variable </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; };<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$closure</span><span class="keyword">();<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">test</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global </span><span class="default">$variable</span><span class="keyword">; </span><span class="comment">//Note the scope added here <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$variable </span><span class="keyword">= </span><span class="string">"second"</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$closure</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">test</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
prints:<br />
first<br />
second<br />
<br />
tested with php 5.3.1</span>
</code></div>
  </div>
 </div>
 <a name="95778"></a>
 <div class="note">
  <strong class='user'>kwilson at shuttlebox dot net</strong>
  <a href="#95778" class="date">21-Jan-2010 07:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using the global keyword apparently pulls variables from the scope where the function was created, not where it is executed. <br />
<br />
Example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; $variable </span><span class="keyword">= </span><span class="string">"first"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$closure </span><span class="keyword">= function() {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; global </span><span class="default">$variable</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$variable </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; };<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$closure</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">test</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$variable </span><span class="keyword">= </span><span class="string">"second"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$closure</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">test</span><span class="keyword">(</span><span class="default">$closure</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Will print:<br />
<br />
first<br />
first</span>
</code></div>
  </div>
 </div>
 <a name="95124"></a>
 <div class="note">
  <strong class='user'>puskulcu at gmail dot com</strong>
  <a href="#95124" class="date">14-Dec-2009 12:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
hello there!<br />
here is a little code which shows use of the closures as event handlers:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; </span><span class="keyword">class </span><span class="default">Button<br />
&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$OnBeforeClick</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$OnAfterClick</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; public </span><span class="default">$Name</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp;&nbsp; function </span><span class="default">Button</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">Name </span><span class="keyword">= </span><span class="string">'MyButton'</span><span class="keyword">;&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; public function </span><span class="default">Click</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">DoBeforeClick</span><span class="keyword">();<br />
&nbsp;&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; echo </span><span class="string">'Click!'</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">DoAfterClick</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">DoBeforeClick</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">OnBeforeClick</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$Event </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">OnBeforeClick</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$Event</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; private function </span><span class="default">DoAfterClick</span><span class="keyword">()<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">OnAfterClick</span><span class="keyword">))<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$Event </span><span class="keyword">= </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">OnAfterClick</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$Event</span><span class="keyword">(</span><span class="default">$this</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp; }<br />
&nbsp; <br />
&nbsp; </span><span class="comment">//eclipse may warn here about syntax error but no problem, it runs well.<br />
&nbsp; </span><span class="default">$BeforeClickEventHandler </span><span class="keyword">= function(</span><span class="default">$Sender</span><span class="keyword">) { echo </span><span class="default">$Sender</span><span class="keyword">-&gt;</span><span class="default">Name </span><span class="keyword">. </span><span class="string">' (Before Click)'</span><span class="keyword">; };&nbsp; <br />
&nbsp; </span><span class="default">$AfterClickEventHandler </span><span class="keyword">= function(</span><span class="default">$Sender</span><span class="keyword">) { echo </span><span class="default">$Sender</span><span class="keyword">-&gt;</span><span class="default">Name </span><span class="keyword">. </span><span class="string">' (After Click)'</span><span class="keyword">; };&nbsp; <br />
&nbsp; <br />
&nbsp; </span><span class="default">$MyWidget </span><span class="keyword">= new </span><span class="default">Button</span><span class="keyword">();<br />
&nbsp; </span><span class="default">$MyWidget</span><span class="keyword">-&gt;</span><span class="default">OnBeforeClick </span><span class="keyword">= </span><span class="default">$BeforeClickEventHandler</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$MyWidget</span><span class="keyword">-&gt;</span><span class="default">OnAfterClick </span><span class="keyword">= </span><span class="default">$AfterClickEventHandler</span><span class="keyword">;<br />
&nbsp; </span><span class="default">$MyWidget</span><span class="keyword">-&gt;</span><span class="default">Click</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
output:<br />
MyButton (Before Click)<br />
Click!<br />
MyButton (After Click)<br />
<br />
i hope you find this useful.<br />
regards.<br />
emre</span>
</code></div>
  </div>
 </div>
 <a name="94804"></a>
 <div class="note">
  <strong class='user'>rob at ubrio dot us</strong>
  <a href="#94804" class="date">25-Nov-2009 10:20</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You can always call protected members using the __call() method - similar to how you hack around this in Ruby using send.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">class </span><span class="default">Fun<br />
</span><span class="keyword">{<br />
&nbsp;protected function </span><span class="default">debug</span><span class="keyword">(</span><span class="default">$message</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp;&nbsp; echo </span><span class="string">"DEBUG: $message\n"</span><span class="keyword">;<br />
&nbsp;}<br />
<br />
&nbsp;public function </span><span class="default">yield_something</span><span class="keyword">(</span><span class="default">$callback</span><span class="keyword">)<br />
&nbsp;{<br />
&nbsp;&nbsp; return </span><span class="default">$callback</span><span class="keyword">(</span><span class="string">"Soemthing!!"</span><span class="keyword">);<br />
&nbsp;}<br />
<br />
&nbsp;public function </span><span class="default">having_fun</span><span class="keyword">()<br />
&nbsp;{<br />
&nbsp;&nbsp; </span><span class="default">$self </span><span class="keyword">=&amp; </span><span class="default">$this</span><span class="keyword">;<br />
&nbsp;&nbsp; return </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">yield_something</span><span class="keyword">(function(</span><span class="default">$data</span><span class="keyword">) use (&amp;</span><span class="default">$self</span><span class="keyword">)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$self</span><span class="keyword">-&gt;</span><span class="default">debug</span><span class="keyword">(</span><span class="string">"Doing stuff to the data"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="comment">// do something with $data<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$self</span><span class="keyword">-&gt;</span><span class="default">debug</span><span class="keyword">(</span><span class="string">"Finished doing stuff with the data."</span><span class="keyword">);<br />
&nbsp;&nbsp; });<br />
&nbsp;}<br />
<br />
&nbsp;</span><span class="comment">// Ah-Ha!<br />
&nbsp;</span><span class="keyword">public function </span><span class="default">__call</span><span class="keyword">(</span><span class="default">$method</span><span class="keyword">, </span><span class="default">$args </span><span class="keyword">= array())<br />
&nbsp;{<br />
&nbsp;&nbsp; if(</span><span class="default">is_callable</span><span class="keyword">(array(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">)))<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">call_user_func_array</span><span class="keyword">(array(</span><span class="default">$this</span><span class="keyword">, </span><span class="default">$method</span><span class="keyword">), </span><span class="default">$args</span><span class="keyword">);<br />
&nbsp;}<br />
}<br />
<br />
</span><span class="default">$fun </span><span class="keyword">= new </span><span class="default">Fun</span><span class="keyword">();<br />
echo </span><span class="default">$fun</span><span class="keyword">-&gt;</span><span class="default">having_fun</span><span class="keyword">();<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94313"></a>
 <div class="note">
  <strong class='user'>mike at blueroot dot co dot uk</strong>
  <a href="#94313" class="date">28-Oct-2009 08:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To recursively call a closure, use this code.<br />
<br />
<span class="default">&lt;?php<br />
$recursive </span><span class="keyword">= function () use (&amp;</span><span class="default">$recursive</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// The function is now available as $recursive<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;<br />
</span><br />
This DOES NOT WORK<br />
<br />
<span class="default">&lt;?php<br />
$recursive </span><span class="keyword">= function () use (</span><span class="default">$recursive</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// The function is now available as $recursive<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="94037"></a>
 <div class="note">
  <strong class='user'>kukoman at pobox dot sk</strong>
  <a href="#94037" class="date">13-Oct-2009 05:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
be aware of&nbsp; Fatal error: Using $this when not in object context when using in closures<br />
<br />
<a href="http://wiki.php.net/rfc/closures/removal-of-this" rel="nofollow" target="_blank">http://wiki.php.net/rfc/closures/removal-of-this</a></span>
</code></div>
  </div>
 </div>
 <a name="93935"></a>
 <div class="note">
  <strong class='user'>gerard at visei dot nl</strong>
  <a href="#93935" class="date">07-Oct-2009 07:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The text above the third example tries to explain that anonymous functions can inherit variables from the parent scope, but fails to properly explain how this is done: namely using the "use" keyword in the function definition.<br />
<br />
The following page has a much more detailed explanation of closures in PHP 5.3:<br />
<a href="http://wiki.php.net/rfc/closures" rel="nofollow" target="_blank">http://wiki.php.net/rfc/closures</a></span>
</code></div>
  </div>
 </div>
 <a name="92818"></a>
 <div class="note">
  <strong class='user'>dave at mausner dot us</strong>
  <a href="#92818" class="date">10-Aug-2009 02:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Ulderico had it almost right.&nbsp; To avoid confusing the interpreter, when using a simple closure stored in a $variable, you must invoke the nameless function using the function syntax.<br />
<br />
<span class="default">&lt;?php <br />
$helloworld </span><span class="keyword">= function(){ <br />
&nbsp;&nbsp;&nbsp; return </span><span class="string">"each hello world is different... "</span><span class="keyword">.</span><span class="default">date</span><span class="keyword">(</span><span class="string">"His"</span><span class="keyword">); <br />
}; <br />
<br />
echo </span><span class="default">$helloworld</span><span class="keyword">( ); <br />
</span><span class="default">?&gt;</span> <br />
<br />
Note the empty actual-parameter list in the "echo".&nbsp; NOW IT WORKS.</span>
</code></div>
  </div>
 </div>
 <a name="92664"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#92664" class="date">03-Aug-2009 02:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you want to check whether you're dealing with a closure specifically and not a string or array callback you can do this:<br />
<br />
<span class="default">&lt;?php<br />
$isAClosure </span><span class="keyword">= </span><span class="default">is_callable</span><span class="keyword">(</span><span class="default">$thing</span><span class="keyword">) &amp;&amp; </span><span class="default">is_object</span><span class="keyword">(</span><span class="default">$thing</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92554"></a>
 <div class="note">
  <strong class='user'>tom at r dot je</strong>
  <a href="#92554" class="date">29-Jul-2009 03:51</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Unfortunately, you can't get a pointer to a function, the only function pointers are ones which use anonymous functions as they're created.<br />
<br />
This wont work:<br />
<br />
<span class="default">&lt;?php<br />
$info </span><span class="keyword">= </span><span class="default">phpinfo</span><span class="keyword">;<br />
</span><span class="default">$info</span><span class="keyword">();<br />
<br />
</span><span class="comment">//or<br />
<br />
</span><span class="keyword">function </span><span class="default">foo</span><span class="keyword">() {<br />
echo </span><span class="string">'bar'</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">$foo </span><span class="keyword">= </span><span class="default">foo</span><span class="keyword">;<br />
</span><span class="default">$foo</span><span class="keyword">();<br />
</span><span class="default">?&gt;<br />
</span><br />
Because of the behavior of $foo(), it will assume $foo is a string, and try to run the function with the name stored in the string.</span>
</code></div>
  </div>
 </div>
 <a name="91871"></a>
 <div class="note">
  <strong class='user'>mcm dot matt at gmail dot com</strong>
  <a href="#91871" class="date">30-Jun-2009 05:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example using uasort.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Usual method.<br />
</span><span class="keyword">function </span><span class="default">cmp</span><span class="keyword">(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return(</span><span class="default">$a </span><span class="keyword">&gt; </span><span class="default">$b</span><span class="keyword">);<br />
}<br />
</span><span class="default">uasort</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">, </span><span class="string">'cmp'</span><span class="keyword">);<br />
<br />
</span><span class="comment">// New<br />
</span><span class="default">uasort</span><span class="keyword">(</span><span class="default">$array</span><span class="keyword">, function(</span><span class="default">$a</span><span class="keyword">, </span><span class="default">$b</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; return(</span><span class="default">$a </span><span class="keyword">&gt; </span><span class="default">$b</span><span class="keyword">);<br />
});<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="91610"></a>
 <div class="note">
  <strong class='user'>a dot schaffhirt at sedna-soft dot de</strong>
  <a href="#91610" class="date">19-Jun-2009 02:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When using anonymous functions as properties in Classes, note that there are three name scopes: one for constants, one for properties and one for methods. That means, you can use the same name for a constant, for a property and for a method at a time.<br />
<br />
Since a property can be also an anonymous function as of PHP 5.3.0, an oddity arises when they share the same name, not meaning that there would be any conflict.<br />
<br />
Consider the following example:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">class </span><span class="default">MyClass </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; const </span><span class="default">member </span><span class="keyword">= </span><span class="default">1</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public </span><span class="default">$member</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">member </span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"method 'member'"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; public function </span><span class="default">__construct </span><span class="keyword">() {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$this</span><span class="keyword">-&gt;</span><span class="default">member </span><span class="keyword">= function () {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="string">"anonymous function 'member'"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; };<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">header</span><span class="keyword">(</span><span class="string">"Content-Type: text/plain"</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$myObj </span><span class="keyword">= new </span><span class="default">MyClass</span><span class="keyword">();<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">MyClass</span><span class="keyword">::</span><span class="default">member</span><span class="keyword">);&nbsp; </span><span class="comment">// int(1)<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">member</span><span class="keyword">);&nbsp;&nbsp; </span><span class="comment">// object(Closure)#2 (0) {}<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">member</span><span class="keyword">()); </span><span class="comment">// string(15) "method 'member'"<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$myMember </span><span class="keyword">= </span><span class="default">$myObj</span><span class="keyword">-&gt;</span><span class="default">member</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">var_dump</span><span class="keyword">(</span><span class="default">$myMember</span><span class="keyword">());&nbsp; &nbsp; &nbsp; </span><span class="comment">// string(27) "anonymous function 'member'"<br />
</span><span class="default">?&gt;<br />
</span><br />
That means, regular method invocations work like expected and like before. The anonymous function instead, must be retrieved into a variable first (just like a property) and can only then be invoked.<br />
<br />
Best regards,</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=functions.anonymous&amp;redirect=http://www.php.net/manual/en/functions.anonymous.php"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=functions.anonymous&amp;redirect=http://www.php.net/manual/en/functions.anonymous.php">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/functions.anonymous.php">show source</a> |
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