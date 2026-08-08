<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: continue - Manual</title>
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
 <link rel="prev" href="control-structures.break.php" />
 <link rel="next" href="control-structures.switch.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/continue" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/control-structures.continue.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{B9RA34XE}" />
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
 <li class="active"><a href="control-structures.continue.php">continue</a></li>
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
  <a href="control-structures.switch.php">switch<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.break.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />break</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.continue.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/control-structures.continue.php">Brazilian Portuguese</option>
    <option value="zh/control-structures.continue.php">Chinese (Simplified)</option>
    <option value="fr/control-structures.continue.php">French</option>
    <option value="de/control-structures.continue.php">German</option>
    <option value="ja/control-structures.continue.php">Japanese</option>
    <option value="pl/control-structures.continue.php">Polish</option>
    <option value="ro/control-structures.continue.php">Romanian</option>
    <option value="ru/control-structures.continue.php">Russian</option>
    <option value="fa/control-structures.continue.php">Persian</option>
    <option value="es/control-structures.continue.php">Spanish</option>
    <option value="tr/control-structures.continue.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="control-structures.continue" class="sect1">
 <h2 class="title"><em>continue</em></h2>
 <p class="verinfo">(PHP 4, PHP 5)</p>
 <p class="simpara">
  <em>continue</em> is used within looping structures to
  skip the rest of the current loop iteration and continue execution
  at the condition evaluation and then the beginning of the next iteration.
 </p>
 <blockquote class="note"><p><strong class="note">Note</strong>: 
  <span class="simpara">
   Note that in PHP the
   <a href="control-structures.switch.php" class="link">switch</a> statement is
   considered a looping structure for the purposes of
   <em>continue</em>.
  </span>
 </p></blockquote>
 <p class="simpara">
  <em>continue</em> accepts an optional numeric argument
  which tells it how many levels of enclosing loops it should skip
  to the end of. The default value is <em>1</em>, thus skipping
  to the end of the current loop.
 </p>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">while&nbsp;(list(</span><span style="color: #0000BB">$key</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;=&nbsp;</span><span style="color: #0000BB">each</span><span style="color: #007700">(</span><span style="color: #0000BB">$arr</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(!(</span><span style="color: #0000BB">$key&nbsp;</span><span style="color: #007700">%&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">))&nbsp;{&nbsp;</span><span style="color: #FF8000">//&nbsp;skip&nbsp;odd&nbsp;members<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">continue;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">do_something_odd</span><span style="color: #007700">(</span><span style="color: #0000BB">$value</span><span style="color: #007700">);<br />}<br /><br /></span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />while&nbsp;(</span><span style="color: #0000BB">$i</span><span style="color: #007700">++&nbsp;&lt;&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Outer&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;while&nbsp;(</span><span style="color: #0000BB">1</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Middle&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;while&nbsp;(</span><span style="color: #0000BB">1</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Inner&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;continue&nbsp;</span><span style="color: #0000BB">3</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"This&nbsp;never&nbsp;gets&nbsp;output.&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Neither&nbsp;does&nbsp;this.&lt;br&nbsp;/&gt;\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

  </div>
 </p>
 <p class="para">
  Omitting the semicolon after <em>continue</em> can lead to
  confusion. Here&#039;s an example of what you shouldn&#039;t do.
 </p>
 <p class="para">
  <div class="informalexample">
   <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">for&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">5</span><span style="color: #007700">;&nbsp;++</span><span style="color: #0000BB">$i</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$i&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">)<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;continue<br />&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"</span><span style="color: #0000BB">$i</span><span style="color: #DD0000">\n"</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
   </div>

   <p class="para">
    One can expect the result to be:
   </p>
   <div class="example-contents screen">
<div class="cdata"><pre>
0
1
3
4
</pre></div>
   </div>
   <p class="para">
    but this script will output:
   </p>
   <div class="example-contents screen">
<div class="cdata"><pre>
2
</pre></div>
   </div>
   <p class="para">
    because the entire <em>continue print &quot;$i\n&quot;;</em> is evaluated
    as a single expression, and so  <span class="function"><a href="function.print.php" class="function">print</a></span> is called only
    when <em>$i == 2</em> is true. (The return value of
    <em>print</em> is passed to <em>continue</em> as the
    numeric argument.)
   </p>
  </div>
 </p>
 <p class="para">
  <table class="doctable table">
   <caption><strong>Changelog for <em>continue</em></strong></caption>
   
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
       <em>continue 0;</em> is no longer valid. In previous versions it was interpreted
       the same as <em>continue 1;</em>.
      </td>
     </tr>

     <tr>
      <td>5.4.0</td>
      <td>
       Removed the ability to pass in variables (e.g., <em>$num = 2; continue $num;</em>)
       as the numerical argument.
      </td>
     </tr>

    </tbody>
   
  </table>

 </p>
</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="control-structures.switch.php">switch<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="control-structures.break.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />break</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/control-structures.continue.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=control-structures.continue&amp;redirect=@w{B9RA34XE}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.continue&amp;redirect=@w{B9RA34XE}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>continue</strong>
 </div><div id="allnotes">
 <a name="108913"></a>
 <div class="note">
  <strong class='user'>maik penz</strong>
  <a href="#108913" class="date">04-Jun-2012 09:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Please note that with PHP 5.4 continue 0; will fail with<br />
<br />
PHP Fatal error:&nbsp; 'continue' operator accepts only positive numbers<br />
<br />
(same is true for break).</span>
</code></div>
  </div>
 </div>
 <a name="105057"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#105057" class="date">25-Jul-2011 09:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">print_primes_between</span><span class="keyword">(</span><span class="default">$x</span><span class="keyword">,</span><span class="default">$y</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">$x</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;=</span><span class="default">$y</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++) <br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for(</span><span class="default">$j</span><span class="keyword">= </span><span class="default">2</span><span class="keyword">; </span><span class="default">$j </span><span class="keyword">&lt; </span><span class="default">$i</span><span class="keyword">; </span><span class="default">$j</span><span class="keyword">++)&nbsp; if(</span><span class="default">$i</span><span class="keyword">%</span><span class="default">$j</span><span class="keyword">==</span><span class="default">0</span><span class="keyword">) continue </span><span class="default">2</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$i</span><span class="keyword">.</span><span class="string">","</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This function, using continue syntax, is to print prime numbers between given numbers, x and y.<br />
For example, print_primes_between(10,20) will output:<br />
<br />
11,13,17,19,23,29,</span>
</code></div>
  </div>
 </div>
 <a name="104015"></a>
 <div class="note">
  <strong class='user'>skippychalmers at gmail dot com</strong>
  <a href="#104015" class="date">17-May-2011 05:56</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To state the obvious, it should be noted, that the optional param defaults to 1 (effectively).</span>
</code></div>
  </div>
 </div>
 <a name="102657"></a>
 <div class="note">
  <strong class='user'>rjsteinert.com</strong>
  <a href="#102657" class="date">26-Feb-2011 09:22</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The most basic example that print "13", skipping over 2.<br />
<br />
<span class="default">&lt;?php<br />
$arr </span><span class="keyword">= array(</span><span class="default">1</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">, </span><span class="default">3</span><span class="keyword">);<br />
foreach(</span><span class="default">$arr </span><span class="keyword">as </span><span class="default">$number</span><span class="keyword">) {<br />
&nbsp; if(</span><span class="default">$number </span><span class="keyword">== </span><span class="default">2</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; continue;<br />
&nbsp; }<br />
&nbsp; print </span><span class="default">$number</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="96945"></a>
 <div class="note">
  <strong class='user'>jaimthorn at yahoo dot com</strong>
  <a href="#96945" class="date">24-Mar-2010 06:36</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The remark "in PHP the switch statement is considered a looping structure for the purposes of continue" near the top of this page threw me off, so I experimented a little using the following code to figure out what the exact semantics of continue inside a switch is:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">for( </span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">3</span><span class="keyword">; ++ </span><span class="default">$i </span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">' ['</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">, </span><span class="string">'] '</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; switch( </span><span class="default">$i </span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">0</span><span class="keyword">: echo </span><span class="string">'zero'</span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">1</span><span class="keyword">: echo </span><span class="string">'one' </span><span class="keyword">; </span><span class="default">XXXX</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; case </span><span class="default">2</span><span class="keyword">: echo </span><span class="string">'two' </span><span class="keyword">; break;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">' &lt;' </span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">, </span><span class="string">'&gt; '</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
For XXXX I filled in<br />
<br />
- continue 1<br />
- continue 2<br />
- break 1<br />
- break 2<br />
<br />
and observed the different results.&nbsp; This made me come up with the following one-liner that describes the difference between break and continue:<br />
<br />
continue resumes execution just before the closing curly bracket ( } ), and break resumes execution just after the closing curly bracket.<br />
<br />
Corollary: since a switch is not (really) a looping structure, resuming execution just before a switch's closing curly bracket has the same effect as using a break statement.&nbsp; In the case of (for, while, do-while) loops, resuming execution just prior their closing curly brackets means that a new iteration is started --which is of course very unlike the behavior of a break statement.<br />
<br />
In the one-liner above I ignored the existence of parameters to break/continue, but the one-liner is also valid when parameters are supplied.</span>
</code></div>
  </div>
 </div>
 <a name="95620"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#95620" class="date">12-Jan-2010 03:08</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Example regarding the condition at the end of the loop and continue:<br />
<span class="default">&lt;?php <br />
$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
do {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">printf</span><span class="keyword">(</span><span class="string">'%d '</span><span class="keyword">, </span><span class="default">$i</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; continue;<br />
} while( ++</span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">10</span><span class="keyword">);<br />
<br />
</span><span class="comment">// Output: 0 1 2 3 4 5 6 7 8 9<br />
</span><span class="default">?&gt;<br />
</span>It gets executed all the time regardless that continue is placed before the while() statement. That does not get skipped.</span>
</code></div>
  </div>
 </div>
 <a name="90323"></a>
 <div class="note">
  <strong class='user'>Nikolay Ermolenko</strong>
  <a href="#90323" class="date">16-Apr-2009 05:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Using continue and break:<br />
<br />
<span class="default">&lt;?php<br />
$stack </span><span class="keyword">= array(</span><span class="string">'first'</span><span class="keyword">, </span><span class="string">'second'</span><span class="keyword">, </span><span class="string">'third'</span><span class="keyword">, </span><span class="string">'fourth'</span><span class="keyword">, </span><span class="string">'fifth'</span><span class="keyword">);<br />
<br />
foreach(</span><span class="default">$stack </span><span class="keyword">AS </span><span class="default">$v</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$v </span><span class="keyword">== </span><span class="string">'second'</span><span class="keyword">)continue;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$v </span><span class="keyword">== </span><span class="string">'fourth'</span><span class="keyword">)break;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$v</span><span class="keyword">.</span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
}<br />
</span><span class="comment">/*<br />
<br />
first<br />
third<br />
<br />
*/<br />
<br />
</span><span class="default">$stack2 </span><span class="keyword">= array(</span><span class="string">'one'</span><span class="keyword">=&gt;</span><span class="string">'first'</span><span class="keyword">, </span><span class="string">'two'</span><span class="keyword">=&gt;</span><span class="string">'second'</span><span class="keyword">, </span><span class="string">'three'</span><span class="keyword">=&gt;</span><span class="string">'third'</span><span class="keyword">, </span><span class="string">'four'</span><span class="keyword">=&gt;</span><span class="string">'fourth'</span><span class="keyword">, </span><span class="string">'five'</span><span class="keyword">=&gt;</span><span class="string">'fifth'</span><span class="keyword">);<br />
foreach(</span><span class="default">$stack2 </span><span class="keyword">AS </span><span class="default">$k</span><span class="keyword">=&gt;</span><span class="default">$v</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$v </span><span class="keyword">== </span><span class="string">'second'</span><span class="keyword">)continue;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$k </span><span class="keyword">== </span><span class="string">'three'</span><span class="keyword">)continue;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">$v </span><span class="keyword">== </span><span class="string">'fifth'</span><span class="keyword">)break;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">$k</span><span class="keyword">.</span><span class="string">' ::: '</span><span class="keyword">.</span><span class="default">$v</span><span class="keyword">.</span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
}<br />
</span><span class="comment">/*<br />
<br />
one ::: first<br />
four ::: fourth<br />
<br />
*/<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="85684"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#85684" class="date">11-Sep-2008 10:04</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The continue keyword can skip division by zero:<br />
<span class="default">&lt;?php<br />
$i </span><span class="keyword">= </span><span class="default">100</span><span class="keyword">;<br />
while (</span><span class="default">$i </span><span class="keyword">&gt; -</span><span class="default">100</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">--;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$i </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; echo (</span><span class="default">200 </span><span class="keyword">/ </span><span class="default">$i</span><span class="keyword">) . </span><span class="string">"&lt;br /&gt;"</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80075"></a>
 <div class="note">
  <strong class='user'>Geekman</strong>
  <a href="#80075" class="date">27-Dec-2007 05:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For clarification, here are some examples of continue used in a while/do-while loop, showing that it has no effect on the conditional evaluation element.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Outputs "1 ".<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
while (</span><span class="default">$i </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"$i "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$i </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">) continue;<br />
}<br />
<br />
</span><span class="comment">// Outputs "1 2 ".<br />
</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
do {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$i</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"$i "</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$i </span><span class="keyword">== </span><span class="default">2</span><span class="keyword">) continue;<br />
} while (</span><span class="default">$i </span><span class="keyword">== </span><span class="default">1</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Both code snippets would behave exactly the same without continue.</span>
</code></div>
  </div>
 </div>
 <a name="71892"></a>
 <div class="note">
  <strong class='user'>tufan dot oezduman at gmail dot com</strong>
  <a href="#71892" class="date">21-Dec-2006 04:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
a possible explanation for the behavior of continue in included scripts mentioned by greg and dedlfix above may be the following line of the "return" documentation: "If the current script file was include()ed or require()ed, then control is passed back to the calling file." <br />
The example of greg produces an error since page2.php does not contain any loop-operations. <br />
<br />
So the only way to give the control back to the loop-operation&nbsp; in page1.php would be a return.</span>
</code></div>
  </div>
 </div>
 <a name="68193"></a>
 <div class="note">
  <strong class='user'>szrrya at yahoo dot com</strong>
  <a href="#68193" class="date">17-Jul-2006 02:18</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Documentation states:<br />
<br />
"continue is used within looping structures to skip the rest of the current loop iteration"<br />
<br />
Current functionality treats switch structures as looping in regards to continue.&nbsp; It has the same effect as break.<br />
<br />
The following code is an example:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">for (</span><span class="default">$i1 </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i1 </span><span class="keyword">&lt; </span><span class="default">2</span><span class="keyword">; </span><span class="default">$i1</span><span class="keyword">++) {<br />
&nbsp; </span><span class="comment">// Loop 1.<br />
&nbsp; </span><span class="keyword">for (</span><span class="default">$i2 </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i2 </span><span class="keyword">&lt; </span><span class="default">2</span><span class="keyword">; </span><span class="default">$i2</span><span class="keyword">++) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Loop 2.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">switch (</span><span class="default">$i2 </span><span class="keyword">% </span><span class="default">2</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp;&nbsp; case </span><span class="default">0</span><span class="keyword">:<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; continue;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; break;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; print </span><span class="string">'[' </span><span class="keyword">. </span><span class="default">$i2 </span><span class="keyword">. </span><span class="string">']&lt;br&gt;'</span><span class="keyword">;<br />
&nbsp; }<br />
&nbsp; print </span><span class="default">$i1 </span><span class="keyword">. </span><span class="string">'&lt;br&gt;'</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
This outputs the following:<br />
[0]<br />
[1]<br />
0<br />
[0]<br />
[1]<br />
1<br />
<br />
Switch is documented as a block of if...elseif... statements, so you might expect the following output:<br />
[1]<br />
0<br />
[1]<br />
1<br />
<br />
This output requires you to either change the switch to an if or use the numerical argument and treat the switch as one loop.</span>
</code></div>
  </div>
 </div>
 <a name="62070"></a>
 <div class="note">
  <strong class='user'>Rene</strong>
  <a href="#62070" class="date">18-Feb-2006 12:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
(only) the reason that is given on the "Continue with missing semikolon" example is wrong.<br />
<br />
the script will output "2" because the missing semikolon causes that the "print"-call is executed only if the "if" statement is true. It has nothing to to with "what" the "print"-call would return or not return, but the returning value can cause to skip to the end of higher level Loops if any call is used that will return a bigger number than 1.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">continue print </span><span class="string">"$i\n"</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
because of the optional argument, the script will not run into a "unexpected T_PRINT" error. It will not run into an error, too, if the call after continue does return anything but a number.<br />
<br />
i suggest to change it from:<br />
because the return value of the print() call is int(1), and it will look like the optional numeric argument mentioned above.<br />
<br />
to<br />
because the print() call will look like the optional numeric argument mentioned above.</span>
</code></div>
  </div>
 </div>
 <a name="60080"></a>
 <div class="note">
  <strong class='user'>net_navard at yahoo dot com</strong>
  <a href="#60080" class="date">25-Dec-2005 09:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hello firends<br />
<br />
It is said in manually:<br />
continue also accepts an optional numeric argument which tells it how many levels of enclosing loops it should .<br />
<br />
In order to understand better this,An example for that:<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="comment">/*continue also accepts an optional numeric argument which <br />
&nbsp;&nbsp;&nbsp; tells it how many levels of enclosing loops it should skip.*/<br />
<br />
</span><span class="keyword">for(</span><span class="default">$k</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$k</span><span class="keyword">&lt;</span><span class="default">2</span><span class="keyword">;</span><span class="default">$k</span><span class="keyword">++)<br />
{</span><span class="comment">//First loop<br />
<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">for(</span><span class="default">$j</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$j</span><span class="keyword">&lt;</span><span class="default">2</span><span class="keyword">;</span><span class="default">$j</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; {</span><span class="comment">//Second loop<br />
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">for(</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">4</span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp; &nbsp;&nbsp; {</span><span class="comment">//Third loop<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">$i</span><span class="keyword">&gt;</span><span class="default">2</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; continue </span><span class="default">2</span><span class="keyword">;</span><span class="comment">// If $i &gt;2 ,Then it skips to the Second loop(level 2),And starts the next step,<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="string">"$i\n"</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
}<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Merry's christmas :)<br />
&nbsp;&nbsp;&nbsp; <br />
With regards,Hossein</span>
</code></div>
  </div>
 </div>
 <a name="49456"></a>
 <div class="note">
  <strong class='user'>dedlfix gives me a hint</strong>
  <a href="#49456" class="date">28-Jan-2005 06:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
a possible solution for <br />
greg AT laundrymat.tv<br />
<br />
I've got the same problem as Greg<br />
and now it works very fine by using<br />
return() instead of continue.<br />
<br />
It seems, that you have to use return()<br />
if you have a file included and<br />
you want to continue with the next loop</span>
</code></div>
  </div>
 </div>
 <a name="49028"></a>
 <div class="note">
  <strong class='user'>greg AT laundrymat.tv</strong>
  <a href="#49028" class="date">14-Jan-2005 08:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
You using continue in a file included in a loop will produce an error.&nbsp; For example:<br />
<br />
//page1.php<br />
for($x=0;$x&lt;10;$x++)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; include('page2.php');&nbsp; &nbsp; <br />
}<br />
<br />
//page2.php<br />
<br />
if($x==5)<br />
&nbsp;&nbsp;&nbsp; continue;<br />
else <br />
&nbsp;&nbsp; print $x;<br />
<br />
it should print<br />
<br />
"012346789" no five, but it produces an error:<br />
<br />
Cannot break/continue 1 level in etc.</span>
</code></div>
  </div>
 </div>
 <a name="42282"></a>
 <div class="note">
  <strong class='user'>www.derosetechnologies.com</strong>
  <a href="#42282" class="date">10-May-2004 08:58</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the same way that one can append a number to the end of a break statement to indicate the "loop" level upon which one wishes to 'break' , one can append a number to the end of a 'continue' statement to acheive the same goal. Here's a quick example:<br />
<br />
&lt;?<br />
&nbsp;&nbsp;&nbsp; for ($i = 0;$i&lt;3;$i++) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo "Start Of I loop\n";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; for ($j=0;;$j++) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if ($j &gt;= 2) continue 2; // This "continue" applies to the "$i" loop <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; echo "I : $i J : $j"."\n";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo "End\n";<br />
&nbsp;&nbsp;&nbsp; }<br />
?&gt;<br />
<br />
The output here is:<br />
Start Of I loop<br />
I : 0 J : 0<br />
I : 0 J : 1<br />
Start Of I loop<br />
I : 1 J : 0<br />
I : 1 J : 1<br />
Start Of I loop<br />
I : 2 J : 0<br />
I : 2 J : 1<br />
<br />
For more information, see the php manual's entry for the 'break' statement.</span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=control-structures.continue&amp;redirect=@w{B9RA34XE}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=control-structures.continue&amp;redirect=@w{B9RA34XE}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/control-structures.continue.php">show source</a> |
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