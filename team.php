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
<pre style="font-family:monospace">
(in random order)

=-..-=-..-=-..-=[  Code  ]=-..-=-..-=-..-=

* ghoulsblade          * hagish
* merlin               * something rotten
* shiranai

=-..-=-..-=-..-=[  Gfx   ]=-..-=-..-=-..-=

* isha                 * elara
* hagish               * ghoulsblade

=-..-=-..-=-..-=[ Admin  ]=-..-=-..-=-..-=

* hagish               * ghoulsblade

=-..-=-..-=-..-=[ Helfer ]=-..-=-..-=-..-=

* forty                * masterhunter
* ishka                * darian
* arpan

=-..-=-..-=-..-=[ Layout ]=-..-=-..-=-..-=

+ sowas haben wir nicht ;) und wenn doch, 
  dann stammt es von den Entwicklern
* Nr 4: ray (kray-c.net)

=-..-=-..-=-..-=[ Greets ]=-..-=-..-=-..-=

* talia (für viel viel faq)
* blob (für moralische Unterstützung
        und den Schrott im alten Forum)
* lil_spack
* felix + jan
* cairon
* großkonsul
* saturnine
* n3
* blackvelvet
* nhin
* benus
* ray
* Ishka (für seine Hilfe in ML)
* den griechen neben der uni und der sushi mensch
* alle geeks und anime freaks auf der erdenscheibe
* das internet, weil wir alle nun so toll vernetzt sind
* gott, dafür daß er kaffee und tee gemacht hat
* hetzner.de (weil sie das beste Rechenzentrum der Welt sind)
* slayradio.org (weil das der beste Sender der Welt ist)
* real life
* ach ja und spielt nicht so viel WoW
</pre>
<?php include("footer.php"); ?>
