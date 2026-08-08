<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head profile="@w{B8XXCD23}">
 <title>PHP: Variables From External Sources - Manual</title>
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
 <link rel="prev" href="language.variables.variable.php" />
 <link rel="next" href="language.constants.php" />
 <link rel="schema.dc" href="@w{RNCDA8N4}" />
 <link rel="schema.rdfs" href="@w{XGTVB7JY}" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/variables.external" />
 <link rel="license" href="@w{G88D3FDX}" about="#content" />
 <link rel="canonical" href="http://php.net/manual/en/language.variables.external.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="@w{TGPHQAYS}" />
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
 <li><a href="language.variables.variable.php">Variable variables</a></li>
 <li class="active"><a href="language.variables.external.php">Variables From External Sources</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">
<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="language.constants.php">Constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.variable.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variable variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.external.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/language.variables.external.php">Brazilian Portuguese</option>
    <option value="zh/language.variables.external.php">Chinese (Simplified)</option>
    <option value="fr/language.variables.external.php">French</option>
    <option value="de/language.variables.external.php">German</option>
    <option value="ja/language.variables.external.php">Japanese</option>
    <option value="pl/language.variables.external.php">Polish</option>
    <option value="ro/language.variables.external.php">Romanian</option>
    <option value="ru/language.variables.external.php">Russian</option>
    <option value="fa/language.variables.external.php">Persian</option>
    <option value="es/language.variables.external.php">Spanish</option>
    <option value="tr/language.variables.external.php">Turkish</option>
    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="@w{XWTW8VF8}" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="language.variables.external" class="sect1">
   <h2 class="title">Variables From External Sources</h2>
   
   <div class="sect2" id="language.variables.external.form">
    <h3 class="title">HTML Forms (GET and POST)</h3>

    <p class="simpara">
     When a form is submitted to a PHP script, the information from 
     that form is automatically made available to the script.  There 
     are many ways to access this information, for example:
    </p>

    <p class="para">
     <div class="example" id="example-106">
      <p><strong>Example #1 A simple HTML form</strong></p>
      <div class="example-contents">
<div class="htmlcode"><pre class="htmlcode">&lt;form action=&quot;foo.php&quot; method=&quot;post&quot;&gt;
    Name:  &lt;input type=&quot;text&quot; name=&quot;username&quot; /&gt;&lt;br /&gt;
    Email: &lt;input type=&quot;text&quot; name=&quot;email&quot; /&gt;&lt;br /&gt;
    &lt;input type=&quot;submit&quot; name=&quot;submit&quot; value=&quot;Submit me!&quot; /&gt;
&lt;/form&gt;</pre>
</div>
      </div>

     </div>
    </p>

    <p class="para">
     Depending on your particular setup and personal preferences, there 
     are many ways to access data from your HTML forms.  Some examples are:
    </p>

    
    <p class="para">
     <div class="example" id="example-107">
      <p><strong>Example #2 Accessing data from a simple POST HTML form</strong></p>
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php&nbsp;<br /></span><span style="color: #FF8000">//&nbsp;Available&nbsp;since&nbsp;PHP&nbsp;4.1.0<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$_POST</span><span style="color: #007700">[</span><span style="color: #DD0000">'username'</span><span style="color: #007700">];<br />&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$_REQUEST</span><span style="color: #007700">[</span><span style="color: #DD0000">'username'</span><span style="color: #007700">];<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">import_request_variables</span><span style="color: #007700">(</span><span style="color: #DD0000">'p'</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'p_'</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$p_username</span><span style="color: #007700">;<br /><br /></span><span style="color: #FF8000">//&nbsp;As&nbsp;of&nbsp;PHP&nbsp;5.0.0,&nbsp;these&nbsp;long&nbsp;predefined&nbsp;variables&nbsp;can&nbsp;be<br />//&nbsp;disabled&nbsp;with&nbsp;the&nbsp;register_long_arrays&nbsp;directive.<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$HTTP_POST_VARS</span><span style="color: #007700">[</span><span style="color: #DD0000">'username'</span><span style="color: #007700">];<br /><br /></span><span style="color: #FF8000">//&nbsp;Available&nbsp;if&nbsp;the&nbsp;PHP&nbsp;directive&nbsp;register_globals&nbsp;=&nbsp;on.&nbsp;As&nbsp;of&nbsp;<br />//&nbsp;PHP&nbsp;4.2.0&nbsp;the&nbsp;default&nbsp;value&nbsp;of&nbsp;register_globals&nbsp;=&nbsp;off.<br />//&nbsp;Using/relying&nbsp;on&nbsp;this&nbsp;method&nbsp;is&nbsp;not&nbsp;preferred.<br /><br />&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">echo&nbsp;</span><span style="color: #0000BB">$username</span><span style="color: #007700">;<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
      </div>

     </div>
    </p>
    <p class="para">
     Using a GET form is similar except you&#039;ll use the appropriate
     GET predefined variable instead. GET also applies to the
     <em>QUERY_STRING</em> (the information after the &#039;?&#039; in a URL).  So,
     for example, <em>http://www.example.com/test.php?id=3</em>
     contains GET data which is accessible with <var class="varname"><var class="varname"><a href="reserved.variables.get.php" class="classname">$_GET['id']</a></var></var>.
     See also <var class="varname"><var class="varname"><a href="reserved.variables.request.php" class="classname">$_REQUEST</a></var></var> and 
      <span class="function"><a href="function.import-request-variables.php" class="function">import_request_variables()</a></span>.
    </p>

    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      <a href="language.variables.superglobals.php" class="link">Superglobal arrays</a>, 
      like <var class="varname"><var class="varname"><a href="reserved.variables.post.php" class="classname">$_POST</a></var></var> and <var class="varname"><var class="varname"><a href="reserved.variables.get.php" class="classname">$_GET</a></var></var>, became 
      available in PHP 4.1.0
     </p>
    </p></blockquote>

    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      Dots and spaces in variable names are converted to underscores. For
      example <em>&lt;input name=&quot;a.b&quot; /&gt;</em> becomes
      <em>$_REQUEST[&quot;a_b&quot;]</em>.
     </p>
    </p></blockquote>

    <p class="para">
     As shown, before PHP 4.2.0 the default value for <a href="ini.core.php#ini.register-globals" class="link">register_globals</a>
     was <em class="emphasis">on</em>.  The PHP 
     community is encouraging all to not rely on this directive 
     as it&#039;s preferred to assume it&#039;s <em class="emphasis">off</em> and code 
     accordingly.
    </p>

    <blockquote class="note"><p><strong class="note">Note</strong>: 
     <p class="para">
      The <a href="info.configuration.php#ini.magic-quotes-gpc" class="link">magic_quotes_gpc</a> 
      configuration directive affects Get, Post and Cookie values.  If 
      turned on, value (It&#039;s &quot;PHP!&quot;) will automagically become (It\&#039;s \&quot;PHP!\&quot;).
      Escaping is needed for DB insertion.  See also 
       <span class="function"><a href="function.addslashes.php" class="function">addslashes()</a></span>,  <span class="function"><a href="function.stripslashes.php" class="function">stripslashes()</a></span> and 
      <a href="sybase.configuration.php#ini.magic-quotes-sybase" class="link">magic_quotes_sybase</a>.
     </p>
    </p></blockquote>
    
    <p class="simpara">
     PHP also understands arrays in the context of form variables 
     (see the <a href="faq.html.php" class="link">related faq</a>).  You may, 
     for example, group related variables together, or use this 
     feature to retrieve values from a multiple select input.  For 
     example, let&#039;s post a form to itself and upon submission display 
     the data:
    </p>

    <p class="para">
     <div class="example" id="example-108">
      <p><strong>Example #3 More complex form variables</strong></p>
      <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$_POST</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'&lt;pre&gt;'</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">htmlspecialchars</span><span style="color: #007700">(</span><span style="color: #0000BB">print_r</span><span style="color: #007700">(</span><span style="color: #0000BB">$_POST</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">));<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">'&lt;/pre&gt;'</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">?&gt;<br /></span>&lt;form&nbsp;action=""&nbsp;method="post"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;Name:&nbsp;&nbsp;&lt;input&nbsp;type="text"&nbsp;name="personal[name]"&nbsp;/&gt;&lt;br&nbsp;/&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;Email:&nbsp;&lt;input&nbsp;type="text"&nbsp;name="personal[email]"&nbsp;/&gt;&lt;br&nbsp;/&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;Beer:&nbsp;&lt;br&nbsp;/&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;select&nbsp;multiple&nbsp;name="beer[]"&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;value="warthog"&gt;Warthog&lt;/option&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;value="guinness"&gt;Guinness&lt;/option&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option&nbsp;value="stuttgarter"&gt;Stuttgarter&nbsp;Schwabenbräu&lt;/option&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;/select&gt;&lt;br&nbsp;/&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;input&nbsp;type="submit"&nbsp;value="submit&nbsp;me!"&nbsp;/&gt;<br />&lt;/form&gt;</span>
</code></div>
      </div>

     </div>
    </p>

    <div class="sect3" id="language.variables.external.form.submit">
     <h4 class="title">IMAGE SUBMIT variable names</h4>

     <p class="simpara">
      When submitting a form, it is possible to use an image instead
      of the standard submit button with a tag like:
     </p>

     <div class="informalexample">
      <div class="example-contents">
<div class="htmlcode"><pre class="htmlcode">&lt;input type=&quot;image&quot; src=&quot;image.gif&quot; name=&quot;sub&quot; /&gt;</pre>
</div>
      </div>

     </div>

     <p class="simpara">
      When the user clicks somewhere on the image, the accompanying
      form will be transmitted to the server with two additional
      variables, <var class="varname"><var class="varname">sub_x</var></var> and <var class="varname"><var class="varname">sub_y</var></var>.
      These contain the coordinates of the
      user click within the image.  The experienced may note that the
      actual variable names sent by the browser contains a period
      rather than an underscore, but PHP converts the period to an
      underscore automatically.
     </p>
    </div>

   </div>

   <div class="sect2" id="language.variables.external.cookies">
    <h3 class="title">HTTP Cookies</h3>

    <p class="simpara">
     PHP transparently supports HTTP cookies as defined by <a href="http://www.faqs.org/rfcs/rfc6265" class="link external">&raquo;&nbsp;RFC 6265</a>.  Cookies are a
     mechanism for storing data in the remote browser and thus
     tracking or identifying return users.  You can set cookies using
     the  <span class="function"><a href="function.setcookie.php" class="function">setcookie()</a></span> function.  Cookies are part of
     the HTTP header, so the SetCookie function must be called before
     any output is sent to the browser.  This is the same restriction
     as for the  <span class="function"><a href="function.header.php" class="function">header()</a></span> function.  Cookie data 
     is then available in the appropriate cookie data arrays, such 
     as <var class="varname"><var class="varname"><a href="reserved.variables.cookies.php" class="classname">$_COOKIE</a></var></var>, <var class="varname"><var class="varname">$HTTP_COOKIE_VARS</var></var> 
     as well as in <var class="varname"><var class="varname"><a href="reserved.variables.request.php" class="classname">$_REQUEST</a></var></var>.  See the 
      <span class="function"><a href="function.setcookie.php" class="function">setcookie()</a></span> manual page for more details and 
     examples.
    </p>

    <p class="simpara">
     If you wish to assign multiple values to a single cookie variable, you 
     may assign it as an array.  For example:
    </p>

    <div class="informalexample">
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />&nbsp;&nbsp;setcookie</span><span style="color: #007700">(</span><span style="color: #DD0000">"MyCookie[foo]"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Testing&nbsp;1'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">time</span><span style="color: #007700">()+</span><span style="color: #0000BB">3600</span><span style="color: #007700">);<br />&nbsp;&nbsp;</span><span style="color: #0000BB">setcookie</span><span style="color: #007700">(</span><span style="color: #DD0000">"MyCookie[bar]"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'Testing&nbsp;2'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">time</span><span style="color: #007700">()+</span><span style="color: #0000BB">3600</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>
    
    <p class="simpara">
     That will create two separate cookies although <var class="varname"><var class="varname">MyCookie</var></var> will now 
     be a single array in your script.  If you want to set just one cookie 
     with multiple values, consider using  <span class="function"><a href="function.serialize.php" class="function">serialize()</a></span> or 
      <span class="function"><a href="function.explode.php" class="function">explode()</a></span> on the value first.
    </p>

    <p class="simpara">
     Note that a cookie will replace a previous cookie by the same
     name in your browser unless the path or domain is different.  So,
     for a shopping cart application you may want to keep a counter
     and pass this along.  i.e.
    </p>

    <div class="example" id="example-109">
     <p><strong>Example #4 A  <span class="function"><a href="function.setcookie.php" class="function">setcookie()</a></span> example</strong></p>
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /></span><span style="color: #007700">if&nbsp;(isset(</span><span style="color: #0000BB">$_COOKIE</span><span style="color: #007700">[</span><span style="color: #DD0000">'count'</span><span style="color: #007700">]))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$count&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$_COOKIE</span><span style="color: #007700">[</span><span style="color: #DD0000">'count'</span><span style="color: #007700">]&nbsp;+&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$count&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />}<br /></span><span style="color: #0000BB">setcookie</span><span style="color: #007700">(</span><span style="color: #DD0000">'count'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$count</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">time</span><span style="color: #007700">()+</span><span style="color: #0000BB">3600</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">setcookie</span><span style="color: #007700">(</span><span style="color: #DD0000">"Cart[</span><span style="color: #0000BB">$count</span><span style="color: #DD0000">]"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$item</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">time</span><span style="color: #007700">()+</span><span style="color: #0000BB">3600</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

    </div>

   </div>

   <div class="sect2" id="language.variables.external.dot-in-names">
    <h3 class="title">Dots in incoming variable names</h3>

    <p class="para">
     Typically, PHP does not alter the names of variables when they
     are passed into a script. However, it should be noted that the
     dot (period, full stop) is not a valid character in a PHP
     variable name. For the reason, look at it:
     <div class="example-contents">
<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />$varname</span><span style="color: #007700">.</span><span style="color: #0000BB">ext</span><span style="color: #007700">;&nbsp;&nbsp;</span><span style="color: #FF8000">/*&nbsp;invalid&nbsp;variable&nbsp;name&nbsp;*/<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code></div>
     </div>

     Now, what the parser sees is a variable named
     <var class="varname"><var class="varname">$varname</var></var>, followed by the string concatenation
     operator, followed by the barestring (i.e. unquoted string which
     doesn&#039;t match any known key or reserved words) &#039;ext&#039;. Obviously,
     this doesn&#039;t have the intended result.
    </p>

    <p class="para">
     For this reason, it is important to note that PHP will
     automatically replace any dots in incoming variable names with
     underscores.
    </p>

   </div>

   <div class="sect2" id="language.variables.determining-type-of">
    <h3 class="title">Determining variable types</h3>

    <p class="para">
     Because PHP determines the types of variables and converts them
     (generally) as needed, it is not always obvious what type a given
     variable is at any one time.  PHP includes several functions
     which find out what type a variable is, such as:
      <span class="function"><a href="function.gettype.php" class="function">gettype()</a></span>,  <span class="function"><a href="function.is-array.php" class="function">is_array()</a></span>,
      <span class="function"><a href="function.is-float.php" class="function">is_float()</a></span>,  <span class="function"><a href="function.is-int.php" class="function">is_int()</a></span>,
      <span class="function"><a href="function.is-object.php" class="function">is_object()</a></span>, and
      <span class="function"><a href="function.is-string.php" class="function">is_string()</a></span>.  See also the chapter on 
     <a href="language.types.php" class="link">Types</a>.
    </p>
   </div>

  </div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="language.constants.php">Constants<img src="@w{GVN7ETSY}" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="language.variables.variable.php"><img src="@w{KX8YRRP2}" alt="&lt;" width="11" height="7" />Variable variables</a>
 </span>
 <hr />
 <span class="lastupdated">[<a href="https://edit.php.net/?project=PHP&amp;perm=en/language.variables.external.php">edit</a>] Last updated: Fri, 27 Jul 2012</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>
<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=language.variables.external&amp;redirect=@w{TGPHQAYS}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.external&amp;redirect=@w{TGPHQAYS}">add a note</a></small></span>
  <small>User Contributed Notes</small>
  <strong>Variables From External Sources</strong>
 </div><div id="allnotes">
 <a name="105228"></a>
 <div class="note">
  <strong class='user'>walf</strong>
  <a href="#105228" class="date">03-Aug-2011 06:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
WARNING! replacement of spaces and dots does not occur in array keys.<br />
<br />
E.g. If you have<br />
&lt;input name="a. b[x. y]" value="foo" /&gt;<br />
<br />
var_dump($_POST);<br />
gives<br />
array(1) {<br />
&nbsp; ["a__b"]=&gt;<br />
&nbsp; array(1) {<br />
&nbsp;&nbsp;&nbsp; ["x. y"]=&gt;<br />
&nbsp;&nbsp;&nbsp; string(3) "foo"<br />
&nbsp; }<br />
}</span>
</code></div>
  </div>
 </div>
 <a name="94607"></a>
 <div class="note">
  <strong class='user'>POSTer</strong>
  <a href="#94607" class="date">13-Nov-2009 02:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here's a simple function to give you an uncorrupted version of $_POST:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// Function to fix up PHP's messing up POST input containing dots, etc.<br />
</span><span class="keyword">function </span><span class="default">getRealPOST</span><span class="keyword">() {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$pairs </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">"&amp;"</span><span class="keyword">, </span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="string">"php://input"</span><span class="keyword">));<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$vars </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$pairs </span><span class="keyword">as </span><span class="default">$pair</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$nv </span><span class="keyword">= </span><span class="default">explode</span><span class="keyword">(</span><span class="string">"="</span><span class="keyword">, </span><span class="default">$pair</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$name </span><span class="keyword">= </span><span class="default">urldecode</span><span class="keyword">(</span><span class="default">$nv</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$value </span><span class="keyword">= </span><span class="default">urldecode</span><span class="keyword">(</span><span class="default">$nv</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$vars</span><span class="keyword">[</span><span class="default">$name</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$vars</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="81080"></a>
 <div class="note">
  <strong class='user'>Anonymous</strong>
  <a href="#81080" class="date">13-Feb-2008 06:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The full list of field-name characters that PHP converts to _ (underscore) is the following (not just dot):<br />
chr(32) ( ) (space)<br />
chr(46) (.) (dot)<br />
chr(91) ([) (open square bracket)<br />
chr(128) - chr(159) (various)<br />
<br />
PHP irreversibly modifies field names containing these characters in an attempt to maintain compatibility with the deprecated register_globals feature.</span>
</code></div>
  </div>
 </div>
 <a name="77344"></a>
 <div class="note">
  <strong class='user'>vierubino dot r3m0oFdisB1T at gmail dot com</strong>
  <a href="#77344" class="date">24-Aug-2007 09:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When you are using checkboxes to submit multiple choices, there is no need to use the complex method further down the page where you assign a unique name to each checkbox.<br />
<br />
Instead, just name each checkbox as the same array, e.g.:<br />
<br />
&lt;input type="checkbox" name="items[]" value="foo" /&gt;<br />
&lt;input type="checkbox" name="items[]" value="bar" /&gt;<br />
&lt;input type="checkbox" name="items[]" value="baz" /&gt;<br />
<br />
This way your $_POST["items"] variable will return as an array containing all and only the checkboxes that were clicked on.</span>
</code></div>
  </div>
 </div>
 <a name="74775"></a>
 <div class="note">
  <strong class='user'>t.montg AT gmail DOT com</strong>
  <a href="#74775" class="date">26-Apr-2007 05:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For anyone else having trouble figuring out how to access values in a SELECT element from a POST or GET form, you can't set the "id" attribute to the same thing as your "name" attribute.&nbsp; i.e. don't do this:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="comment">//Not so good<br />
&nbsp; </span><span class="keyword">&lt;</span><span class="default">select multiple</span><span class="keyword">=</span><span class="string">"multiple" </span><span class="default">id</span><span class="keyword">=</span><span class="string">"selectElem" </span><span class="default">name</span><span class="keyword">=</span><span class="string">"selectElem[]"</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"ham"</span><span class="keyword">&gt;</span><span class="default">Ham</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"cheese"</span><span class="keyword">&gt;</span><span class="default">Cheese</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"hamcheese"</span><span class="keyword">&gt;</span><span class="default">Ham </span><span class="keyword">and </span><span class="default">Cheese</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp; &lt;/</span><span class="default">select</span><span class="keyword">&gt;<br />
</span><span class="default">?&gt;<br />
</span><br />
If you do the above, the variable $_POST['selectElem'] will not be set.&nbsp; Instead, either change the id or name attribute so that they are dissimilar.&nbsp; i.e. do this:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp; </span><span class="comment">//So good (notice the new "id" value)<br />
&nbsp; </span><span class="keyword">&lt;</span><span class="default">select multiple</span><span class="keyword">=</span><span class="string">"multiple" </span><span class="default">id</span><span class="keyword">=</span><span class="string">"selectElemId" </span><span class="default">name</span><span class="keyword">=</span><span class="string">"selectElem[]"</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"ham"</span><span class="keyword">&gt;</span><span class="default">Ham</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"cheese"</span><span class="keyword">&gt;</span><span class="default">Cheese</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;</span><span class="default">option value</span><span class="keyword">=</span><span class="string">"hamcheese"</span><span class="keyword">&gt;</span><span class="default">Ham </span><span class="keyword">and </span><span class="default">Cheese</span><span class="keyword">&lt;/</span><span class="default">option</span><span class="keyword">&gt;<br />
&nbsp; &lt;/</span><span class="default">select</span><span class="keyword">&gt;<br />
</span><span class="default">?&gt;<br />
</span><br />
Then you can access the value(s) of the SELECT element through the array $_POST['selectElem'][] or $_GET['selectElem'][].&nbsp; It took me quite some time to figure out the problem.</span>
</code></div>
  </div>
 </div>
 <a name="63295"></a>
 <div class="note">
  <strong class='user'>ch1902uk at hotmail dot com</strong>
  <a href="#63295" class="date">18-Mar-2006 11:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Regarding image input buttons, above where it says:<br />
<br />
"When the user clicks somewhere on the image, the accompanying form will be transmitted to the server with two *additional* variables, sub_x and sub_y. These contain the coordinates of the user click within the image." <br />
<br />
This is the case with Firefox (and probably other standards browsers), however my experience with Internet Explorer is that when image inputs are clicked, they only submit the location of the click on the button and *not* the name of the input.<br />
<br />
So if you have a form to move/delete entries like this<br />
<br />
entry[]&nbsp; [delete_0] [up_0] [down_0]<br />
entry[]&nbsp;&nbsp; [delete_1] [up_1] [down_1]<br />
entry[]&nbsp;&nbsp; [delete_2] [up_2] [down_2]<br />
<br />
Then submitting the form in firefox will give you post variables such as <br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; $_POST</span><span class="keyword">[</span><span class="string">'delete_2'</span><span class="keyword">];&nbsp;&nbsp; </span><span class="comment">// "Delete" - button value<br />
&nbsp;&nbsp; </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'delete_2_x'</span><span class="keyword">];&nbsp;&nbsp; </span><span class="comment">// 23 - x coord <br />
&nbsp;&nbsp; </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'delete_2_y'</span><span class="keyword">];&nbsp;&nbsp; </span><span class="comment">// 3 - y coord <br />
</span><span class="default">?&gt;<br />
</span><br />
In IE you only get<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; $_POST</span><span class="keyword">[</span><span class="string">'delete_2_x'</span><span class="keyword">];&nbsp;&nbsp; </span><span class="comment">// 23 - x coord <br />
&nbsp;&nbsp; </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'delete_2_y'</span><span class="keyword">];&nbsp;&nbsp; </span><span class="comment">// 3 - y coord <br />
</span><span class="default">?&gt;<br />
</span><br />
So if you are checking for what button was clicked do something like this<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp; </span><span class="keyword">for (</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">count</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'entry'</span><span class="keyword">]); </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp;&nbsp; if (isset(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'delete_' </span><span class="keyword">. </span><span class="default">$i </span><span class="keyword">. </span><span class="string">'_x'</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; </span><span class="comment">// do delete<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="58228"></a>
 <div class="note">
  <strong class='user'>aescomputer AT yahoo DOT com</strong>
  <a href="#58228" class="date">27-Oct-2005 11:01</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here are two usefull functions for forms:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// clean html tags out of url attributes<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!empty(</span><span class="default">$_REQUEST</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$_REQUEST </span><span class="keyword">as </span><span class="default">$x </span><span class="keyword">=&gt; </span><span class="default">$y</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="default">$x</span><span class="keyword">] = </span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">'&lt;'</span><span class="keyword">, </span><span class="string">'&amp;lt;'</span><span class="keyword">, </span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">'&gt;'</span><span class="keyword">, </span><span class="string">'&amp;gt;'</span><span class="keyword">, </span><span class="default">$y</span><span class="keyword">));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
</span><span class="comment">// replace carrage returns in url attributes with &lt;br&gt;<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!empty(</span><span class="default">$_REQUEST</span><span class="keyword">)){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; foreach(</span><span class="default">$_REQUEST </span><span class="keyword">as </span><span class="default">$x </span><span class="keyword">=&gt; </span><span class="default">$y</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$_REQUEST</span><span class="keyword">[</span><span class="default">$x</span><span class="keyword">] = </span><span class="default">str_replace</span><span class="keyword">(</span><span class="string">"\n"</span><span class="keyword">, </span><span class="string">'&lt;br&gt;'</span><span class="keyword">, </span><span class="default">$y</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="52503"></a>
 <div class="note">
  <strong class='user'>krydprz at iit dot edu</strong>
  <a href="#52503" class="date">03-May-2005 01:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This post is with regards to handling forms that have more than one submit button.<br />
<br />
Suppose we have an HTML form with a submit button specified like this:<br />
<br />
&lt;input type="submit" value="Delete" name="action_button"&gt;<br />
<br />
Normally the 'value' attribute of the HTML 'input' tag (in this case "Delete") that creates the submit button can be accessed in PHP after post like this:<br />
<br />
<span class="default">&lt;?php<br />
$_POST</span><span class="keyword">[</span><span class="string">'action_button'</span><span class="keyword">];<br />
</span><span class="default">?&gt;<br />
</span><br />
We of course use the 'name' of the button as an index into the $_POST array.<br />
<br />
This works fine, except when we want to pass more information with the click of this particular button.<br />
<br />
Imagine a scenario where you're dealing with user management in some administrative interface.&nbsp; You are presented with a list of user names queried from a database and wish to add a "Delete" and "Modify" button next to each of the names in the list.&nbsp; Naturally the 'value' of our buttons in the HTML form that we want to display will be "Delete" and "Modify" since that's what we want to appear on the buttons' faceplates.<br />
<br />
Both buttons (Modify and Delete) will be named "action_button" since that's what we want to index the $_POST array with.&nbsp; In other words, the 'name' of the buttons along cannot carry any uniquely identifying information if we want to process them systematically after submit. Since these buttons will exist for every user in the list, we need some further way to distinguish them, so that we know for which user one of the buttons has been pressed.<br />
<br />
Using arrays is the way to go.&nbsp; Assuming that we know the unique numerical identifier of each user, such as their primary key from the database, and we DON'T wish to protect that number from the public, we can make the 'action_button' into an array and use the user's unique numerical identifier as a key in this array.<br />
<br />
Our HTML code to display the buttons will become:<br />
<br />
&lt;input type="submit" value="Delete" name="action_button[0000000002]"&gt;<br />
&lt;input type="submit" value="Modify" name="action_button[0000000002]"&gt;<br />
<br />
The 0000000002 is of course the unique numerical identifier for this particular user.<br />
<br />
Then when we handle this form in PHP we need to do the following to extract both the 'value' of the button ("Delete" or "Modify") and the unique numerical identifier of the user we wish to affect (0000000002 in this case). The following will print either "Modify" or "Delete", as well as the unique number of the user:<br />
<br />
<span class="default">&lt;?php<br />
$submitted_array </span><span class="keyword">= </span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'action_button'</span><span class="keyword">]);<br />
echo (</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'action_button'</span><span class="keyword">][</span><span class="default">$submitted_array</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]] . </span><span class="string">" " </span><span class="keyword">. </span><span class="default">$submitted_array</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">]);<br />
</span><span class="default">?&gt;<br />
</span><br />
$submitted_array[0] carries the 0000000002.<br />
When we index that into the $_POST['action_button'], like we did above, we will extract the string that was used as 'value' in the HTML code 'input' tag that created this button.<br />
<br />
If we wish to protect the unique numerical identifier, we must use some other uniquely identifying attribute of each user. Possibly that attribute should be encrypted when output into the form for greater security.<br />
<br />
Enjoy!</span>
</code></div>
  </div>
 </div>
 <a name="52466"></a>
 <div class="note">
  <strong class='user'>user: &quot;someuser&quot; at mai1server &quot;ua.fm&quot;</strong>
  <a href="#52466" class="date">02-May-2005 12:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Numerous string like:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if (isset(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">"var1"</span><span class="keyword">]))<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$var1</span><span class="keyword">=</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">"var1"</span><span class="keyword">];<br />
else </span><span class="default">$var1</span><span class="keyword">=</span><span class="string">''</span><span class="keyword">;<br />
</span><span class="comment">//...<br />
</span><span class="keyword">if (isset(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">"varN"</span><span class="keyword">]))<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$varN</span><span class="keyword">=</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">"varN"</span><span class="keyword">];<br />
else </span><span class="default">$varN</span><span class="keyword">=</span><span class="string">''</span><span class="keyword">;<br />
</span><span class="default">?&gt;<br />
</span><br />
Can be replaced with:<br />
<br />
<span class="default">&lt;?php<br />
get_superglobal_vars_from_POST</span><span class="keyword">(</span><span class="string">'var1'</span><span class="keyword">,</span><span class="string">'...'</span><span class="keyword">,</span><span class="string">'varN'</span><span class="keyword">);<br />
<br />
function </span><span class="default">get_superglobal_vars_from_POST</span><span class="keyword">()<br />
{<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$numargs </span><span class="keyword">= </span><span class="default">func_num_args</span><span class="keyword">();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$setargs </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="comment">// for counting set variables<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">for (</span><span class="default">$i</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">&lt;</span><span class="default">$numargs</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$varname</span><span class="keyword">=</span><span class="default">func_get_arg</span><span class="keyword">(</span><span class="default">$i</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if (!isset(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="default">$varname</span><span class="keyword">]))<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$result</span><span class="keyword">=</span><span class="string">''</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; else<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$result</span><span class="keyword">=</span><span class="default">$_POST</span><span class="keyword">[</span><span class="default">$varname</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$setargs</span><span class="keyword">++;<br />
&nbsp;&nbsp;&nbsp; }&nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$GLOBALS</span><span class="keyword">[</span><span class="string">"$varname"</span><span class="keyword">]=</span><span class="default">$result</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$setargs</span><span class="keyword">; </span><span class="comment">// who cares?<br />
</span><span class="keyword">}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="52423"></a>
 <div class="note">
  <strong class='user'>tim at timpauly dot com</strong>
  <a href="#52423" class="date">30-Apr-2005 06:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This code module can be added to every form using require_once().<br />
It will process any and all form data, prepending each variable with<br />
a unique identifier (so you know which method was used to get the data).<br />
<br />
My coding could be neater, but this sure makes processing forms much easier!<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment">// -----------------------------------------------------------------<br />
// Basic Data PHP module. This module captures all GET, POST<br />
// and COOKIE data and processes it into variables.<br />
// Coded April, 2005 by Timothy J. Pauly <br />
// -----------------------------------------------------------------<br />
//<br />
// coo_ is prepended to each cookie variable<br />
// get_ is prepended to each GET variable<br />
// pos_ is prepended to each POST variable<br />
// ses_ is prepended to each SESSION variable<br />
// ser_ is prepended to each SERVER variable<br />
<br />
</span><span class="default">session_start</span><span class="keyword">(); </span><span class="comment">// initialize session data<br />
</span><span class="default">$ArrayList </span><span class="keyword">= array(</span><span class="string">"_POST"</span><span class="keyword">, </span><span class="string">"_GET"</span><span class="keyword">, </span><span class="string">"_SESSION"</span><span class="keyword">, </span><span class="string">"_COOKIE"</span><span class="keyword">, </span><span class="string">"_SERVER"</span><span class="keyword">); </span><span class="comment">// create an array of the autoglobal arrays<br />
// we want to process<br />
<br />
</span><span class="keyword">foreach(</span><span class="default">$ArrayList </span><span class="keyword">as </span><span class="default">$gblArray</span><span class="keyword">) </span><span class="comment">// process each array in the array list<br />
</span><span class="keyword">{<br />
&nbsp;&nbsp; </span><span class="default">$prefx </span><span class="keyword">= </span><span class="default">strtolower</span><span class="keyword">(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">$gblArray</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">,</span><span class="default">3</span><span class="keyword">)).</span><span class="string">"_"</span><span class="keyword">; </span><span class="comment">// derive the prepend string <br />
// from the autoglobal type name<br />
&nbsp;&nbsp; </span><span class="default">$tmpArray </span><span class="keyword">= $</span><span class="default">$gblArray</span><span class="keyword">;<br />
&nbsp;&nbsp; </span><span class="default">$keys </span><span class="keyword">= </span><span class="default">array_keys</span><span class="keyword">(</span><span class="default">$tmpArray</span><span class="keyword">); </span><span class="comment">// extract the keys from the array being processed<br />
&nbsp;&nbsp; </span><span class="keyword">foreach(</span><span class="default">$keys </span><span class="keyword">as </span><span class="default">$key</span><span class="keyword">) </span><span class="comment">// process each key<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$arcnt </span><span class="keyword">= </span><span class="default">count</span><span class="keyword">(</span><span class="default">$tmpArray</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">$arcnt </span><span class="keyword">&gt; </span><span class="default">1</span><span class="keyword">) </span><span class="comment">// Break down passed arrays and <br />
// process each element seperately<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">{<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">$lcount </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp;&nbsp; foreach (</span><span class="default">$tmpArray</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] as </span><span class="default">$dval</span><span class="keyword">) <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; { <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$prkey </span><span class="keyword">= </span><span class="default">$prefx</span><span class="keyword">.</span><span class="default">$key</span><span class="keyword">; </span><span class="comment">// create a new key string <br />
// with the prepend string added<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$prdata</span><span class="keyword">[</span><span class="string">'$prkey'</span><span class="keyword">] = </span><span class="default">$dval</span><span class="keyword">; </span><span class="comment">// this step could be eliminated<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="keyword">${</span><span class="default">$prkey</span><span class="keyword">}[</span><span class="default">$lcount</span><span class="keyword">] = </span><span class="default">$prdata</span><span class="keyword">[</span><span class="string">'$prkey'</span><span class="keyword">]; </span><span class="comment">//create new key and insert the data<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">$lcount</span><span class="keyword">++;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else { </span><span class="comment">// process passed single variables<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prkey </span><span class="keyword">= </span><span class="default">$prefx</span><span class="keyword">.</span><span class="default">$key</span><span class="keyword">; </span><span class="comment">// create a new key string <br />
// with the prepend string added<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$prdata</span><span class="keyword">[</span><span class="string">'$prkey'</span><span class="keyword">] = </span><span class="default">$tmpArray</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">]; </span><span class="comment">// insert the data from <br />
// the old array into the new one<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">$</span><span class="default">$prkey </span><span class="keyword">= </span><span class="default">$prdata</span><span class="keyword">[</span><span class="string">'$prkey'</span><span class="keyword">]; </span><span class="comment">// create the newly named <br />
// (prepended) key pair using variable variables :-)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="keyword">}<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
<br />
</span><span class="comment">// -------------------------------------------------------------<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="51727"></a>
 <div class="note">
  <strong class='user'>tmk-php at infeline dot org</strong>
  <a href="#51727" class="date">08-Apr-2005 09:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To handle forms with or without [] you can do something like this:<br />
<br />
<span class="default">&lt;?php<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">repairPost</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// combine rawpost and $_POST ($data) to rebuild broken arrays in $_POST<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$rawpost </span><span class="keyword">= </span><span class="string">"&amp;"</span><span class="keyword">.</span><span class="default">file_get_contents</span><span class="keyword">(</span><span class="string">"php://input"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while(list(</span><span class="default">$key</span><span class="keyword">,</span><span class="default">$value</span><span class="keyword">)= </span><span class="default">each</span><span class="keyword">(</span><span class="default">$data</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$pos </span><span class="keyword">= </span><span class="default">preg_match_all</span><span class="keyword">(</span><span class="string">"/&amp;"</span><span class="keyword">.</span><span class="default">$key</span><span class="keyword">.</span><span class="string">"=([^&amp;]*)/i"</span><span class="keyword">,</span><span class="default">$rawpost</span><span class="keyword">, </span><span class="default">$regs</span><span class="keyword">, </span><span class="default">PREG_PATTERN_ORDER</span><span class="keyword">);&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if((!</span><span class="default">is_array</span><span class="keyword">(</span><span class="default">$value</span><span class="keyword">)) &amp;&amp; (</span><span class="default">$pos </span><span class="keyword">&gt; </span><span class="default">1</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$qform</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = array();<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">; </span><span class="default">$i </span><span class="keyword">&lt; </span><span class="default">$pos</span><span class="keyword">; </span><span class="default">$i</span><span class="keyword">++) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$qform</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">][</span><span class="default">$i</span><span class="keyword">] = </span><span class="default">urldecode</span><span class="keyword">(</span><span class="default">$regs</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">][</span><span class="default">$i</span><span class="keyword">]);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$qform</span><span class="keyword">[</span><span class="default">$key</span><span class="keyword">] = </span><span class="default">$value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">$qform</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// --- MAIN<br />
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$_POST </span><span class="keyword">= </span><span class="default">repairPost</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
The function will check every field in the $_POST with the raw post data and rebuild the arrays that got lost.</span>
</code></div>
  </div>
 </div>
 <a name="50546"></a>
 <div class="note">
  <strong class='user'>Murat TASARSU</strong>
  <a href="#50546" class="date">02-Mar-2005 04:29</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you want your multiple select returned variable in comma seperated form you can use this. hope that helps. regards...<br />
<br />
$myvariable <br />
&nbsp;&nbsp; Array ( [0] =&gt; one [1] =&gt; two [2] =&gt; three ) <br />
turns into<br />
&nbsp;&nbsp; one,two,three<br />
<br />
<span class="default">&lt;?php<br />
$myvariable</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">;<br />
</span><span class="default">$myseperator</span><span class="keyword">=</span><span class="string">""</span><span class="keyword">;<br />
foreach ( </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">"myvariable"</span><span class="keyword">] as </span><span class="default">$v</span><span class="keyword">) {<br />
if (!isset(</span><span class="default">$nofirstcomma</span><span class="keyword">)) </span><span class="default">$nofirstcomma</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; else </span><span class="default">$myseperator</span><span class="keyword">=</span><span class="string">","</span><span class="keyword">;<br />
</span><span class="default">$myvariable </span><span class="keyword">= </span><span class="default">$myvariable</span><span class="keyword">.</span><span class="default">$myseperator</span><span class="keyword">.</span><span class="default">$v</span><span class="keyword">;<br />
}<br />
echo </span><span class="default">$myvariable</span><span class="keyword">;<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="49574"></a>
 <div class="note">
  <strong class='user'>jlratwil at yahoo dot com</strong>
  <a href="#49574" class="date">01-Feb-2005 05:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To get multiple selected (with "multiple" ) lists in &lt;select&gt; tag, make sure that the "name" attribute is added to braces, like this:<br />
<br />
&lt;select multiple="multiple" name="users[]"&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;option value="foo"&gt;Foo&lt;/option&gt;<br />
&nbsp;&nbsp; &nbsp; &lt;option value="bar"&gt;Bar&lt;/option&gt;<br />
&lt;/select&gt;<br />
<br />
When submitted to PHP file (assume that you have a complete form) it will return an array of strings. Otherwise, it will just return the last element of the &lt;select&gt; tag you selected.</span>
</code></div>
  </div>
 </div>
 <a name="49116"></a>
 <div class="note">
  <strong class='user'>__kbanks at (__ignoreunderscores) dot gmail dot com</strong>
  <a href="#49116" class="date">17-Jan-2005 03:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Hi all:<br />
<br />
I'm presently building a solution to programmatically build, view, and validate HTML forms when I came across the ol' PHP non-scalar form variable handling problem.&nbsp; Because my form validator class would really have no way of knowing if a given form variable was meant to be scalar or not, I decided to TREAT ALL PHP FORM VARIABLES AS ARRAYS, i.e., to use the '[]' syntax for all variables, scalar or not.&nbsp; This way, the form designer need not remember to specify the '[]' syntax, and the Form-&gt;HTML transformer need not scan for every input within the form, looking for duplicate names.&nbsp; This also eliminates the risk of losing data you meant to keep in an array but to which you forgot to apply the '[]' syntax.&nbsp; <br />
<br />
You may then either treat all form variables as arrays in your form handling code, or you may, as I intend to do, filter each HTML request through a Front Controller (desc. in Fowler, PoEAA).&nbsp; The Front Controller would convert all arrays of length 1 to a scalar variable and leave multi-element arrays as they are.&nbsp; This should essentially convert PHP's handling of non-scalar form variables to that of ASP's (or slightly better, since multiple form values of the same name will actually be arrays, not just comma-seperated values).&nbsp; <br />
<br />
This way my Form Validator class can just check if the input to validate is an array, and then apply some constraint across each element in the array.</span>
</code></div>
  </div>
 </div>
 <a name="47188"></a>
 <div class="note">
  <strong class='user'>mattij at nitro  fi no at no dot no</strong>
  <a href="#47188" class="date">05-Nov-2004 07:39</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you try to refer or pass HTML-form data which has arrays with javascript remember that you should point to that array like this<br />
<br />
&lt;script type="text/javascript"&gt;<br />
&nbsp;&nbsp;&nbsp; window.opener.document.forms[0]["to[where][we][point]"];<br />
&lt;/script&gt;</span>
</code></div>
  </div>
 </div>
 <a name="43949"></a>
 <div class="note">
  <strong class='user'>lennynyktyk at yahoo dot com</strong>
  <a href="#43949" class="date">09-Jul-2004 02:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When dealing with multiple select boxes and the name=some_name[] so that PHP will understand that is needs to interpet the input as an array an not as a single value. If you want to access this in Javascript you should assign an id attribute to the select box as well as the name attribute. Then proceed to use the id attribute in Javascript to reference the select box and the name attribute to reference the select box in PHP.<br />
Example<br />
<br />
&lt;select multiple id="select_id" name="select_name[]"&gt;<br />
....<br />
<br />
&lt;/select&gt;<br />
<br />
<span class="default">&lt;?PHP<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">echo </span><span class="default">$select_name</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
</span><span class="default">?&gt;<br />
</span><br />
&lt;script language="javascript"&gt;<br />
&nbsp; document.forms[0].select_id.options[0].selected = true;<br />
&lt;/script&gt;<br />
<br />
I hope you get the idea</span>
</code></div>
  </div>
 </div>
 <a name="41025"></a>
 <div class="note">
  <strong class='user'>arjini at mac dot com</strong>
  <a href="#41025" class="date">26-Mar-2004 11:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When dealing with form inputs named_like_this[5] and javascript, instead of trying to get PHP to do something fancy as mentioned below, just try this on the javascript side of things:<br />
<br />
&lt;form name="myForm"&gt;<br />
<br />
&lt;script&gt;<br />
my_fancy_input_name = 'array_of_things[1]';<br />
/* now just refer to it like this in the dom tree <br />
<br />
document[myForm][my_fancy_input_name].value<br />
<br />
etc*/<br />
&lt;/script&gt;<br />
<br />
&lt;input type="text" name="array_of_things[1]" value="1"/&gt;<br />
&lt;/form&gt;<br />
<br />
No fancy PHP, in fact, you shouldn't need to change your PHP at all.</span>
</code></div>
  </div>
 </div>
 <a name="39818"></a>
 <div class="note">
  <strong class='user'>epr3</strong>
  <a href="#39818" class="date">11-Feb-2004 01:40</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
there is more simple to generate the checkbox and recognize<br />
which box is clicked<br />
<br />
<span class="default">&lt;?php <br />
<br />
</span><span class="keyword">echo </span><span class="string">'&lt;script language="JavaScript" type="text/JavaScript"&gt;<br />
<br />
var t = 0;<br />
<br />
&lt;/script&gt;'</span><span class="keyword">;<br />
<br />
for(</span><span class="default">$i </span><span class="keyword">= </span><span class="default">0 </span><span class="keyword">;</span><span class="default">$i </span><span class="keyword">&lt;</span><span class="default">5 </span><span class="keyword">;</span><span class="default">$i</span><span class="keyword">++){<br />
&nbsp;<br />
&nbsp; echo </span><span class="string">'<br />
&nbsp; &lt;script language="JavaScript" type="text/JavaScript"&gt;<br />
&nbsp; <br />
&nbsp; t++;<br />
&nbsp; <br />
&nbsp; document.writeln(t);<br />
&nbsp; document.writeln("&lt;input type=\"checkbox\" name=\"cbx_foo[]\" value=\"" + t + "\"&gt;");<br />
&nbsp; <br />
&nbsp; &lt;/script&gt;<br />
&nbsp; '</span><span class="keyword">; <br />
}<br />
<br />
foreach(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'cbx_foo'</span><span class="keyword">] as </span><span class="default">$value</span><span class="keyword">) {<br />
<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="string">"&lt;BR&gt;You clicked checkbox number " </span><span class="keyword">. </span><span class="default">$value </span><span class="keyword">. </span><span class="string">"\n"</span><span class="keyword">;&nbsp; &nbsp; <br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="39270"></a>
 <div class="note">
  <strong class='user'>jim at jamesdavis dot it</strong>
  <a href="#39270" class="date">22-Jan-2004 11:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
How to pass a numerically indexed array.<br />
This is the part inside the form. Notice that the name is not 'english[$r]' which you would normally write, but 'english[]'. PHP adds the index when it receives the post and it starts at 0.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">for (</span><span class="default">$r</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$r </span><span class="keyword">&lt;= </span><span class="default">count</span><span class="keyword">(</span><span class="default">$english</span><span class="keyword">)-</span><span class="default">1</span><span class="keyword">; </span><span class="default">$r</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; echo </span><span class="string">"&lt;TEXTAREA NAME='english[]'&gt;"</span><span class="keyword">.</span><span class="default">$english</span><span class="keyword">[</span><span class="default">$r</span><span class="keyword">].</span><span class="string">"&lt;/TEXTAREA&gt;"</span><span class="keyword">;&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <br />
}<br />
</span><span class="default">?&gt;<br />
&lt;?php<br />
<br />
</span><span class="keyword">And </span><span class="default">this will get it out at the other end<br />
</span><span class="keyword">function </span><span class="default">retrieve_english</span><span class="keyword">(){<br />
&nbsp;&nbsp;&nbsp; for (</span><span class="default">$r</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">; </span><span class="default">$r </span><span class="keyword">&lt;= </span><span class="default">count</span><span class="keyword">(</span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'english'</span><span class="keyword">])-</span><span class="default">1</span><span class="keyword">; </span><span class="default">$r</span><span class="keyword">++){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'english'</span><span class="keyword">][</span><span class="default">$r</span><span class="keyword">].</span><span class="string">"&lt;BR&gt;"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;<br />
</span><br />
Keys are useful but so are numerical indices!<br />
Cheers everyone</span>
</code></div>
  </div>
 </div>
 <a name="37887"></a>
 <div class="note">
  <strong class='user'>darren at sullivan dot net</strong>
  <a href="#37887" class="date">01-Dec-2003 08:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This function is a simple solution for getting the array of selectes from a checkbox list or a dropdown list out of the Querry String. I took an example posted earlier and simplified it. <br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">multi_post_item</span><span class="keyword">(</span><span class="default">$repeatedString</span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Gets the specified array of multiple selects and/or <br />
&nbsp;&nbsp;&nbsp; // checkboxes from the Query String<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$ArrayOfItems </span><span class="keyword">= array();<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$raw_input_items </span><span class="keyword">= </span><span class="default">split</span><span class="keyword">(</span><span class="string">"&amp;"</span><span class="keyword">, </span><span class="default">$_SERVER</span><span class="keyword">[</span><span class="string">"QUERY_STRING"</span><span class="keyword">]);<br />
&nbsp;&nbsp;&nbsp; foreach (</span><span class="default">$raw_input_items </span><span class="keyword">as </span><span class="default">$input_item</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$itemPair </span><span class="keyword">= </span><span class="default">split</span><span class="keyword">(</span><span class="string">"="</span><span class="keyword">, </span><span class="default">$input_item</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$itemPair</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">] == </span><span class="default">$repeatedString</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$ArrayOfItems</span><span class="keyword">[] = </span><span class="default">$itemPair</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">$ArrayOfItems</span><span class="keyword">;<br />
} <br />
</span><span class="default">?&gt;<br />
</span><br />
Use the name of the field as the agrument. Example:<br />
<br />
<span class="default">&lt;?php<br />
$Order </span><span class="keyword">= </span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'Order'</span><span class="keyword">];<br />
</span><span class="default">$Name </span><span class="keyword">= </span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'Name'</span><span class="keyword">];<br />
</span><span class="default">$States </span><span class="keyword">= </span><span class="default">multi_post_item</span><span class="keyword">(</span><span class="string">'States'</span><span class="keyword">);<br />
</span><span class="default">$Products </span><span class="keyword">= </span><span class="default">multi_post_item</span><span class="keyword">(</span><span class="string">'Products'</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Be sure to check for NULL if there are no selections or boxes checked.</span>
</code></div>
  </div>
 </div>
 <a name="37185"></a>
 <div class="note">
  <strong class='user'>soeren at hattel dot dk</strong>
  <a href="#37185" class="date">06-Nov-2003 04:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The decision in PHP to translate a query string like:<br />
<br />
a=2&amp;a=3&amp;a=4 <br />
<br />
into one single variable a=4 is simply strupid! <br />
<br />
The "wonderful" hack allowing multiple values to be read only if one uses:<br />
<br />
a[]=2&amp;a[]=3&amp;a[]=4<br />
<br />
is - at first sight - a nice feature but soon become a pain in the a..!<br />
<br />
In ASP and ASPX the first situation is handled as:<br />
<br />
a=2,3,4<br />
<br />
which is better than the PHP behaviour but still bad (what if your variable values contain commas?).<br />
<br />
It seems to me that the proper behaviour would be:<br />
<br />
a=2&amp;a=3&amp;a=4<br />
<br />
automatically generates an array with all the variables inside. I know this would require proper error handling but evevy things does anyway!</span>
</code></div>
  </div>
 </div>
 <a name="34820"></a>
 <div class="note">
  <strong class='user'>kevinrlat nospam dot ccs dot neu dot edu</strong>
  <a href="#34820" class="date">07-Aug-2003 10:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
if you use an array of checkboxes to submit info to a database or what have you, be careful of the case when no boxes are checked.&nbsp; for example:<br />
<br />
&lt;form method="post"&gt;<br />
&lt;input type="checkbox" name="checkstuff[]" value="0"&gt;<br />
&lt;input type="checkbox" name="checkstuff[]" value="1"&gt;<br />
&lt;input type="checkbox" name="checkstuff[]" value="2"&gt;<br />
<br />
. . .<br />
<br />
&lt;/form&gt;<br />
<br />
if these are submitted and none are checked, the $_POST['checkstuff'] variable will not contain an empty array, but a NULL value.&nbsp; this bothered me when trying to implode() the values of my checkboxes to insert into a database, i got a warning saying the 2nd argument was the wrong type.&nbsp; <br />
<br />
hope this helps!<br />
-kevin</span>
</code></div>
  </div>
 </div>
 <a name="30866"></a>
 <div class="note">
  <strong class='user'>un shift at yahoo dot com</strong>
  <a href="#30866" class="date">01-Apr-2003 10:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This function takes a recurring form item from php://input and loads it into an array - useful for javascript/dom incompatibility with form_input_item[] names for checkboxes, multiple selects, etc.&nbsp; The fread maxes out at 100k on this one.&nbsp; I guess a more portable option would be pulling in ini_get('post_max_size') and converting it to an integer.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">multi_post_item</span><span class="keyword">(</span><span class="default">$input_item_name</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$array_output </span><span class="keyword">= array();<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$in_handle </span><span class="keyword">= </span><span class="default">fopen</span><span class="keyword">(</span><span class="string">"php://input"</span><span class="keyword">, </span><span class="string">"r"</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; </span><span class="default">$raw_input_items </span><span class="keyword">= </span><span class="default">split</span><span class="keyword">(</span><span class="string">"&amp;"</span><span class="keyword">, </span><span class="default">urldecode</span><span class="keyword">(</span><span class="default">fread</span><span class="keyword">(</span><span class="default">$in_handle</span><span class="keyword">, </span><span class="default">100000</span><span class="keyword">)));<br />
&nbsp;&nbsp; &nbsp; foreach (</span><span class="default">$raw_input_items </span><span class="keyword">as </span><span class="default">$input_item</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// split this item into name/value pair<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$item </span><span class="keyword">= </span><span class="default">split</span><span class="keyword">(</span><span class="string">"="</span><span class="keyword">, </span><span class="default">$input_item</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// form item name<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$item_name </span><span class="keyword">= </span><span class="default">$item</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// form item value<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$item_value </span><span class="keyword">= </span><span class="default">$item</span><span class="keyword">[</span><span class="default">1</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">$item_name </span><span class="keyword">== </span><span class="default">$input_item_name</span><span class="keyword">) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$array_output</span><span class="keyword">[] = </span><span class="default">$item_value</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; }<br />
&nbsp;&nbsp; &nbsp; return </span><span class="default">$array_output</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="30485"></a>
 <div class="note">
  <strong class='user'>vb at bertola dot eu dot org</strong>
  <a href="#30485" class="date">19-Mar-2003 09:38</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For what I understand, since PHP 4.3 it is possible to access the content of a POST request (or other methods as well) as an input stream named php://input, example:<br />
<br />
readfile("php://input");&nbsp;&nbsp; <br />
[to display it]<br />
<br />
or<br />
<br />
$fp = fopen("php://input", "r");&nbsp; &nbsp; <br />
[to open it and then do whatever you want]<br />
<br />
This is very useful to access the content of POST requests which actually have a content (and not just variable-value couples, which appear in $_POST).<br />
<br />
This substitutes the old $HTTP_RAW_POST_DATA variable available in some of the previous 4.x versions. It is available for other upload methods different from POST too, but it is not available for POSTs with multipart/form-data content type, since the file upload handler has already taken care of the content in that case.</span>
</code></div>
  </div>
 </div>
 <a name="30257"></a>
 <div class="note">
  <strong class='user'>mail at paulodeon dot com</strong>
  <a href="#30257" class="date">12-Mar-2003 05:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you have form data that could be coming in via either GET or POST and register_globals is off (as it should be) use the empty() function to find out where the data is coming from, as in the following example<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">if(empty(</span><span class="default">$_GET</span><span class="keyword">)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$clientfilter </span><span class="keyword">= </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'clientfilter'</span><span class="keyword">];<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">$branchfilter </span><span class="keyword">= </span><span class="default">$_POST</span><span class="keyword">[</span><span class="string">'branchfilter'</span><span class="keyword">];<br />
}<br />
if(empty(</span><span class="default">$_POST</span><span class="keyword">)) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$clientfilter </span><span class="keyword">= </span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'clientfilter'</span><span class="keyword">];<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">$branchfilter </span><span class="keyword">= </span><span class="default">$_GET</span><span class="keyword">[</span><span class="string">'branchfilter'</span><span class="keyword">];<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="30019"></a>
 <div class="note">
  <a href="#30019" class="date">04-Mar-2003 07:21</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
"...the dot (period, full stop) is not a valid character in a PHP variable name."<br />
<br />
That's not completely correct, consider this example:<br />
$GLOBALS['foo.bar'] = 'baz';<br />
echo ${'foo.bar'};<br />
This will output baz as expected.</span>
</code></div>
  </div>
 </div>
 <a name="29118"></a>
 <div class="note">
  <strong class='user'>keli at kmdsz dot ro</strong>
  <a href="#29118" class="date">03-Feb-2003 07:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
image type inputs apparently return their "value" argument from Mozilla, but not from IEXplorer... :(<br />
<br />
example:<br />
<br />
&nbsp;&lt;input type="image" name="sb" value="first" src="first.jpg"&gt;<br />
<br />
using a mozilla will give you <br />
&nbsp; $sb="first" AND $sb_x, $sb_y ... whereas from IE there's just no $sb. :(<br />
<br />
[this in short form, as I'm still using trackvars :) ]</span>
</code></div>
  </div>
 </div>
 <a name="23117"></a>
 <div class="note">
  <strong class='user'>steiner277 at charter dot net</strong>
  <a href="#23117" class="date">09-Jul-2002 05:55</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
When accessing variables from a post, do NOT put $_POST['fieldname'] in double quotes or you will get an error message.<br />
<br />
e.g. the following works fine:<br />
<br />
$msg = "The message is:\t" . $_POST['message'] . "\n";<br />
<br />
but the following will cause errors:<br />
<br />
$msg = "The message is:\t$_POST['message']\n";</span>
</code></div>
  </div>
 </div>
 <a name="21752"></a>
 <div class="note">
  <strong class='user'>hjncom at hjncom dot net</strong>
  <a href="#21752" class="date">25-May-2002 03:34</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I think '[' and ']' are valid characters for name attributes.<br />
<br />
<a href="http://www.w3.org/TR/html401/interact/forms.html#h-17.4" rel="nofollow" target="_blank">http://www.w3.org/TR/html401/interact/forms.html#h-17.4</a><br />
-&gt; InputType of 'name' attribute is 'CDATA'(not 'NAME' type)<br />
<br />
<a href="http://www.w3.org/TR/html401/types.html#h-6.2" rel="nofollow" target="_blank">http://www.w3.org/TR/html401/types.html#h-6.2</a><br />
-&gt; about CDATA('name' attribute is not 'NAME' type!)<br />
...CDATA is a sequence of characters from the document character set and may include character entities...<br />
<br />
<a href="http://www.w3.org/TR/html401/sgml/entities.html" rel="nofollow" target="_blank">http://www.w3.org/TR/html401/sgml/entities.html</a><br />
--&gt; about Character entity references in HTML 4<br />
([ - &amp;#91, ] - &amp;#93)</span>
</code></div>
  </div>
 </div>
 <a name="21401"></a>
 <div class="note">
  <strong class='user'>jesper at codecrew dot dk</strong>
  <a href="#21401" class="date">11-May-2002 07:12</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Just to help others with the same stupid problem i have:<br />
<br />
I you use a checkbox in your form, it will only return the value specified in value if it is checked.<br />
<br />
ex. &lt;input type="checkbox" value="yes"&gt;<br />
<br />
in php code you then write<br />
<br />
$checkboxchecked = ($checkbox == "yes");<br />
<br />
I guess :)</span>
</code></div>
  </div>
 </div>
 <a name="18737"></a>
 <div class="note">
  <strong class='user'>a at b dot c dot de</strong>
  <a href="#18737" class="date">03-Feb-2002 04:49</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
As far as whether or not "[]" in name attributes goes, The HTML4.01 specification only requires that it be a case-insensitive CDATA token, which can quite happily include "[]". Leading and trailing whitespace may be trimmed and shouldn't be used.<br />
<br />
It is the id= attribute which is restricted, to a case-sensitive NAME token (not to be confused with a name= attribute).</span>
</code></div>
  </div>
 </div>
 <a name="18655"></a>
 <div class="note">
  <strong class='user'>carl_steinhilber at NOSPAMmentor dot com</strong>
  <a href="#18655" class="date">30-Jan-2002 02:19</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
A group of identically-named checkbox form elements returning an array is a pretty standard feature of HTML forms. It would seem that, if the only way to get it to work is a non-HTML-standard-compliant workaround, it's a problem with PHP.<br />
<br />
Since the array is passed in the header in a post, or the URL in a get, it's the PHP interpretation of those values that's failing.</span>
</code></div>
  </div>
 </div>
 <a name="11873"></a>
 <div class="note">
  <strong class='user'>yasuo_ohgaki at hotmail dot com</strong>
  <a href="#11873" class="date">11-Mar-2001 04:02</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Important:&nbsp; Pay attention to the following security concerns when handling user submitted&nbsp; data :<br />
<br />
<a href="http://www.php.net/manual/en/security.registerglobals.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/security.registerglobals.php</a><br />
<a href="http://www.php.net/manual/en/security.variables.php" rel="nofollow" target="_blank">http://www.php.net/manual/en/security.variables.php</a></span>
</code></div>
  </div>
 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=language.variables.external&amp;redirect=@w{TGPHQAYS}"><img src="@w{WPBKWWJ7}" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=language.variables.external&amp;redirect=@w{TGPHQAYS}">add a note</a></small></div>
</div><br />
 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/language.variables.external.php">show source</a> |
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