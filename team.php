<?php
include("lib.php");
if(isset($f_sid))
{
	sql("DELETE FROM `session` WHERE `sid`='".addslashes($f_sid)."'");
	header("Location: index.php");
	exit;
}

include("header.php");
?>
<span id=team><h1>Team</h1></span>
<pre>
(in random order)

==[ Code ]==
* merlin
* ghoulsblade
* hagish
* something rotten
* shiranai

==[ Gfx ]==
* isha
* elara
* hagish
* ghoulsblade

==[ Contributed Gfx ]==
* forty
* masterhunter

==[ Admin ]==
* hagish
* merlin
* ghoulsblade
* Moreta

==[ Layout ]==
+ sowas haben wir nicht ;) und wenn doch, 
  dann stammt es von den Entwicklern
* Nr 4: ray (kray-c.net)

==[ Greets ]==
* talia (für viel viel faq)
* blob (für moralische Unterstützung und den Schrott im alten Forum)
* lil_spack
* felix + jan
* carion
* großkonsul
* sturnine
* KiliK
* n3
* blackvelvet
* nhin
* benus
* den griechen neben der uni und der sushi mensch
* real life
* alle geeks und anime freaks auf der erdenscheibe
* das internet, weil wir alle nun so toll vernetzt sind
* gott, dafür daß er kaffee und tee gemacht hat
* hetzner.de (weil sie das beste Rechenzentrum der Welt sind)
* dJ SoNiC
* ray

</pre>
<?php include("footer.php"); ?>
