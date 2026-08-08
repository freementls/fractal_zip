<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "@w{QPKF54RE}">
<html xmlns="@w{SZTDMW9J}" xml:lang="en" lang="en">
<head>
 <title>PHP: My PHP.net</title>
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
 <link rel="canonical" href="http://php.net/my.php" />
 <script type="text/javascript" src="@w{4SAB2YT3}"></script>
 <base href="http://www.php.net/my.php" />
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
  </p>
 </form>
</div>

<div id="layout_1">
 <div id="content" class=".">

<form action="/my.php" method="post">
<h1>My PHP.net</h1>

<p>
 This page allows you to customize the PHP.net site to some degree
 to your own liking.
</p>

<p>
 These settings will be active on all official PHP.net mirror sites,
 and are stored using cookies, so you need to have cookies enabled
 to let your settings work.
</p>

<h2>Preferred language</h2>

<p>
 If you use a shortcut or search for a function, the language used
 is determined by checking for the following settings. The list is
 in priority order, the first is the most important. Normally you don't
 need to set your preferred language, as your last seen language is
 always remembered, and is a good estimate on your preferred language
 most of the time.
</p>

<div class="indent">
<table border="0" cellpadding="3" cellspacing="2" class="standard">
 <tr>
  <td class="sub">Your preferred language</td>
  <td><select name="my_lang">
<option value="not_set" selected="selected">Not Set</option>
<option value="en">English</option>
<option value="pt_BR">Brazilian Portuguese</option>
<option value="zh">Chinese (Simplified)</option>
<option value="fr">French</option>
<option value="de">German</option>
<option value="ja">Japanese</option>
<option value="pl">Polish</option>
<option value="ro">Romanian</option>
<option value="ru">Russian</option>
<option value="fa">Persian</option>
<option value="es">Spanish</option>
<option value="tr">Turkish</option>
</select>
</td>
 </tr>
 <tr>
  <td class="sub">Last seen language</td>
  <td>en</td>
 </tr>
 <tr>
  <td class="sub">Your Accept-Language browser setting</td>
  <td>en-us,en;q=0.5</td>
 </tr>
 <tr>
  <td class="sub">The mirror's default language</td>
  <td>en</td>
 </tr>
 <tr>
  <td class="sub">Default</td>
  <td>en</td>
 </tr>
</table>
</div>

<p>
 These settings are only overridden in case you have passed a language
 setting URL parameter or POST data to a page or you are viewing a manual
 page in a particular language. In these cases, the explicit specification
 overrides the language selected from the above list.
</p>

<p>
 The language setting is honored when you use an
 <a href="/urlhowto.php">URL shortcut</a>, when you start
 a function list search on a non-manual page, when you visit
 the <a href="/download-docs.php">manual download</a> or
 <a href="/docs.php">language selection</a> pages, etc.
</p>

<h2>Your country</h2>

<p>
 The PHP.net site and mirror sites try to detect your country
 using the <a href="http://www.directi.com/?site=ip-to-country">Directi
 Ip-to-Country Database</a>. This information is used to mark
 the events in your country specially and to offer close mirror
 sites if possible on the download page and on the mirror listing
 page.
</p>

<div class="indent">
We were unable to detect your country</div>

<h2>URL search fallback</h2>

<p>
 When you try to access a PHP.net page via an URL shortcut, and
 the site is unable to find that particular page, it falls back
 to a documentation search, or a function list lookup, depending on
 your choice. The default is a function list lookup, as most of
 the URL shortcut users try to access function documentation pages.
</p>

<div class="indent">
 Your setting: <input type="radio" name="urlsearch" value="quickref"
 checked="checked" /> Function list search <input type="radio" name="urlsearch" value="manual" /> PHP Documentation search
</div>

<h2>Search field suggestions</h2>

<p>
 Whenever you start a search on a PHP.net page, a list of suggested function
 names starting with the letters you typed in are suggested. If your browser
 has problems with this functionality or you are not interested in these
 suggestions, you can turn them off here. <strong>Note that this feature is
 currently only available on the <a href="/search">search page itself</a>,
 not on any of the other pages.</strong>
</p>

<div class="indent">
 Your setting: <input type="radio" name="hidesuggest" value="0"
 checked="checked" /> Show suggestions <input type="radio" name="hidesuggest" value="1" /> Hide suggestions
</div>

<h2>Mirror site redirection</h2>

<p>
 The www.php.net site redirects users to mirror sites in several cases
 automatically. It tries to find a close mirror first (a mirror in the
 user's country), and if no such mirror is found, it selects one mirror
 randomly. Here you can set one preferred mirror site for yourself in
 case you are not satisfied with the automatic selection.
</p>

<p>
 Please note that in case the site finds your preferred mirror site disabled
 for some reason, it will fall back to the automatic selection procedure, but
 will not alter your preferences, so next time when your selected server works,
 the redirections will lead you there. 
</p>

<div class="indent">
 <select name="mirror">
  <option value="http://ar2.php.net/">Argentina (ar2.php.net)</option>
  <option value="http://am.php.net/">Armenia (am.php.net)</option>
  <option value="http://au.php.net/">Australia (au.php.net)</option>
  <option value="http://au2.php.net/">Australia (au2.php.net)</option>
  <option value="http://at.php.net/">Austria (at.php.net)</option>
  <option value="http://at2.php.net/">Austria (at2.php.net)</option>
  <option value="http://az.php.net/">Azerbaijan (az.php.net)</option>
  <option value="http://bd.php.net/">Bangladesh (bd.php.net)</option>
  <option value="http://be.php.net/">Belgium (be.php.net)</option>
  <option value="http://be2.php.net/">Belgium (be2.php.net)</option>
  <option value="http://br.php.net/">Brazil (br.php.net)</option>
  <option value="http://br2.php.net/">Brazil (br2.php.net)</option>
  <option value="http://bg2.php.net/">Bulgaria (bg2.php.net)</option>
  <option value="http://ca.php.net/">Canada (ca.php.net)</option>
  <option value="http://ca2.php.net/">Canada (ca2.php.net)</option>
  <option value="http://ca3.php.net/">Canada (ca3.php.net)</option>
  <option value="http://cl.php.net/">Chile (cl.php.net)</option>
  <option value="http://cn.php.net/">China (cn.php.net)</option>
  <option value="http://cn2.php.net/">China (cn2.php.net)</option>
  <option value="http://co.php.net/">Colombia (co.php.net)</option>
  <option value="http://hr.php.net/">Croatia (hr.php.net)</option>
  <option value="http://cz.php.net/">Czech Republic (cz.php.net)</option>
  <option value="http://cz2.php.net/">Czech Republic (cz2.php.net)</option>
  <option value="http://dk.php.net/">Denmark (dk.php.net)</option>
  <option value="http://dk2.php.net/">Denmark (dk2.php.net)</option>
  <option value="http://ee.php.net/">Estonia (ee.php.net)</option>
  <option value="http://fi2.php.net/">Finland (fi2.php.net)</option>
  <option value="http://fr.php.net/">France (fr.php.net)</option>
  <option value="http://fr2.php.net/">France (fr2.php.net)</option>
  <option value="http://de.php.net/">Germany (de.php.net)</option>
  <option value="http://de2.php.net/">Germany (de2.php.net)</option>
  <option value="http://de3.php.net/">Germany (de3.php.net)</option>
  <option value="http://gr.php.net/">Greece (gr.php.net)</option>
  <option value="http://gr2.php.net/">Greece (gr2.php.net)</option>
  <option value="http://hk.php.net/">Hong Kong (hk.php.net)</option>
  <option value="http://hk2.php.net/">Hong Kong (hk2.php.net)</option>
  <option value="http://hu.php.net/">Hungary (hu.php.net)</option>
  <option value="http://hu2.php.net/">Hungary (hu2.php.net)</option>
  <option value="http://is.php.net/">Iceland (is.php.net)</option>
  <option value="http://is2.php.net/">Iceland (is2.php.net)</option>
  <option value="http://in.php.net/">India (in.php.net)</option>
  <option value="http://in2.php.net/">India (in2.php.net)</option>
  <option value="http://in3.php.net/">India (in3.php.net)</option>
  <option value="http://id.php.net/">Indonesia (id.php.net)</option>
  <option value="http://id2.php.net/">Indonesia (id2.php.net)</option>
  <option value="http://ir.php.net/">Iran (ir.php.net)</option>
  <option value="http://ir2.php.net/">Iran (ir2.php.net)</option>
  <option value="http://ie.php.net/">Ireland (ie.php.net)</option>
  <option value="http://ie2.php.net/">Ireland (ie2.php.net)</option>
  <option value="http://il.php.net/">Israel (il.php.net)</option>
  <option value="http://it.php.net/">Italy (it.php.net)</option>
  <option value="http://it2.php.net/">Italy (it2.php.net)</option>
  <option value="http://jm2.php.net/">Jamaica (jm2.php.net)</option>
  <option value="http://jp.php.net/">Japan (jp.php.net)</option>
  <option value="http://jp2.php.net/">Japan (jp2.php.net)</option>
  <option value="http://lv.php.net/">Latvia (lv.php.net)</option>
  <option value="http://li.php.net/">Liechtenstein (li.php.net)</option>
  <option value="http://lt.php.net/">Lithuania (lt.php.net)</option>
  <option value="http://lu.php.net/">Luxembourg (lu.php.net)</option>
  <option value="http://my.php.net/">Malaysia (my.php.net)</option>
  <option value="http://mx.php.net/">Mexico (mx.php.net)</option>
  <option value="http://mx2.php.net/">Mexico (mx2.php.net)</option>
  <option value="http://nl.php.net/">Netherlands (nl.php.net)</option>
  <option value="http://nl3.php.net/">Netherlands (nl3.php.net)</option>
  <option value="http://nc.php.net/">New Caledonia (nc.php.net)</option>
  <option value="http://nz.php.net/">New Zealand (nz.php.net)</option>
  <option value="http://no.php.net/">Norway (no.php.net)</option>
  <option value="http://no2.php.net/">Norway (no2.php.net)</option>
  <option value="http://pk1.php.net/">Pakistan (pk1.php.net)</option>
  <option value="http://pa.php.net/">Panama (pa.php.net)</option>
  <option value="http://pa2.php.net/">Panama (pa2.php.net)</option>
  <option value="http://pt.php.net/">Portugal (pt.php.net)</option>
  <option value="http://pt2.php.net/">Portugal (pt2.php.net)</option>
  <option value="http://kr.php.net/">Republic of Korea (kr.php.net)</option>
  <option value="http://kr2.php.net/">Republic of Korea (kr2.php.net)</option>
  <option value="http://md.php.net/">Republic of Moldova (md.php.net)</option>
  <option value="http://ro1.php.net/">Romania (ro1.php.net)</option>
  <option value="http://ro2.php.net/">Romania (ro2.php.net)</option>
  <option value="http://ru2.php.net/">Russian Federation (ru2.php.net)</option>
  <option value="http://sg.php.net/">Singapore (sg.php.net)</option>
  <option value="http://sg2.php.net/">Singapore (sg2.php.net)</option>
  <option value="http://sg3.php.net/">Singapore (sg3.php.net)</option>
  <option value="http://sk.php.net/">Slovakia (sk.php.net)</option>
  <option value="http://si.php.net/">Slovenia (si.php.net)</option>
  <option value="http://si2.php.net/">Slovenia (si2.php.net)</option>
  <option value="http://es.php.net/">Spain (es.php.net)</option>
  <option value="http://se.php.net/">Sweden (se.php.net)</option>
  <option value="http://se2.php.net/">Sweden (se2.php.net)</option>
  <option value="http://ch.php.net/">Switzerland (ch.php.net)</option>
  <option value="http://ch2.php.net/">Switzerland (ch2.php.net)</option>
  <option value="http://tw.php.net/">Taiwan (tw.php.net)</option>
  <option value="http://tw2.php.net/">Taiwan (tw2.php.net)</option>
  <option value="http://th.php.net/">Thailand (th.php.net)</option>
  <option value="http://tr.php.net/">Turkey (tr.php.net)</option>
  <option value="http://tr2.php.net/">Turkey (tr2.php.net)</option>
  <option value="http://ua.php.net/">Ukraine (ua.php.net)</option>
  <option value="http://ua2.php.net/">Ukraine (ua2.php.net)</option>
  <option value="http://uk.php.net/">United Kingdom (uk.php.net)</option>
  <option value="http://uk3.php.net/">United Kingdom (uk3.php.net)</option>
  <option value="http://tz.php.net/">United Republic of Tanzania (tz.php.net)</option>
  <option value="http://us.php.net/">United States (us.php.net)</option>
  <option value="http://us2.php.net/">United States (us2.php.net)</option>
  <option value="http://us3.php.net/">United States (us3.php.net)</option>
  <option value="NONE" selected="selected">Automatic selection (default)</option>
 </select>
</div>


<h2>PHP.net alpha</h2>

<p>
 php.net is undergoing plastic surgery these days. If you want to see
 how the site <strong>may</strong> look in the future, you can join our alpha program.
 <br />
 Comments, feedback and patches should be directed to
 <a href="mailto:php-webmaster@lists.php.net">php-webmaster@lists.php.net</a>.
</p>

<div class="indent">
 <select name="beta">
    <option value="0" selected>Disable</option>
    <option value="1" >Enable</option>
 </select>
</div>

<p class="center">
 <input type="submit" value="Set All Preferences" />
</p>
</form>


 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/my.php">show source</a> |
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