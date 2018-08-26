<?php
  /**
   * Author Yann Pellegrini
   * Date 2011
   * Licence GPLv3
   */
  
   
  if(isset($_GET) && isset($_SESSION['id'])) {
?>
<style>
  #app_guide {
    text-align:justify;
  }
  #app_guide code {
    font-family:consolas;
    color:black;
  }
</style>
<script>
  $('#app_guide')
</script>
<div class="app" id="app_guide">
  <div class="box">
    <h3><img class="icon" src="design/icons/guide.png" />Le guide du joueur de Server Fight</h3>
  </div>
  <div id="guide">
    Vous venez de rejoindre le jeu et ne savez pas par où commencer : suivez le guide !<br />
    <br />
    <h2><img class="icon" src="design/asterisk.png" />Sommaire</h2><br />

    <h4>Les bases de ServerFight</h4>
    <ul>
      <li>Présentation du jeu</li>
      <li>Commencer une partie sur Server Fight</li>
    </ul>

    <h4>Serveurs</h4>
    <ul>
      <li>Bases sur les serveurs</li>
      <li>Particularités de localhost</li>
      <li>Exploiter un serveur</li>
      <li>Le piratage in-game de A à Z</li>
    </ul>

    <h4>Applications</h4><br />

    <h4>Commandes</h4>
    <ul>
      <li>Commandes de base</li>
      <li>Commandes pour les serveurs / sur le piratage</li>
    </ul>
    <br /><br />
    <h2><img class="icon" src="design/asterisk.png" />Les bases de Server Fight</h2><br />

    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Présentation du jeu</h3><br />

    Bienvenue sur Server Fight, jeu de gestion et de simulation de piratage par navigateur !<br />
    Vous allez commencer avec votre premier serveur virtuel, localhost.<br />
    Ce premier serveur vous sera utile pour vous lancer dans le jeu et à avoir toujours une base en cas de petit incident sur vos futurs autres serveurs :p<br />

    A partir de vos serveurs virtuels, vous pourrez :
    <ul>
      <li>Gagner de l’argent avec lequel vous pourrez l’équiper ou acheter d’autres serveurs, et nombre d’autres possibilités que nous découvrirons,</li>
      <li>Ou pirater les serveurs des autres joueurs.</li>
    </ul>

    <h3>Commencer votre partie sur Server Fight.</h3><br />

    Vous avez remarqué que le jeu se présente sous la forme d’un bureau avec des applications.<br />
    La première application à vous être utile sera le Terminal.<br />
    Nous nous en servirons dans un premier temps pour installer sur votre bureau de nouvelles applications .<br /><br />

    Jetons un coup d’oeil sur vos serveurs. Pour cela, l’application ServerManager est disponible grâce à la commande “download servermanager”.<br />
    Puis ouvrez cette nouvelle application. Vous y trouverez la liste de vos serveurs avec leurs caractéristiques.<br />
    Vous pouvez ensuite la refermer : elle ne vous sera véritablement utile que lorsque vous posséderez un “vrai” serveur, un peu plus tard dans le jeu <img class="smiley" src="design/smiley/wink.png" /><br /><br />

    Pour le moment, nous allons apprendre à tirer profit d’un serveur.<br /><br />

    Nous utiliserons 3 commandes de base, très simples <img class="smiley" src="design/smiley/happy.png" /> <code>connect</code>, <code>work</code> et <code>buy</code>.<br />
    <ul>
      <li><code>connect localhost</code> vous permet d’utiliser ce serveur. Les commandes que vous entrerez par la suite pointeront sur le serveur sur lequel vous êtes connecté.
      <li>Votre serveur a un nombre de RAM qui vous permettra de gagner plus d’argent (ou par la suite de mieux se défendre/attaquer les joueurs). 
      <li>La commande <code>buy ram [quantité]</code> est disponible. Si vous venez d’arriver, utilisez vos 500 tokens de départ avec <code>buy ram 5</code> ;)
      <li>
        Puis utilisez la commande <code>work [temps de travail]</code> qui vous rémunérera en tokens proportionnellement au nombre de RAM sur votre serveur. Chaque serveur peut travailler 10 heures virtuelles par jour.<br />
        Faisons par exemple travailler le serveur 5 heures en tapant <code>work 5</code>.
      </li>
    </ul>
    <br />
    C’est fini ?<br />
    Non, pas tout à fait. Vous devriez racheter des RAM avec les tokens que vous venez de gagner ! Tapez <code>buy ram [quantité que vous voulez acheter]</code>.<br />
    Il reste encore 5 heures de travail à votre serveur. Tapez <code>work 5</code>. Vous avez gagné plus que la première fois car vous avez plus de RAM que la dernière fois :)<br />
    <br />
    Vous avez entièrement tiré profit de votre serveur pour aujourd’hui. Vous aurez a nouveau 10 heures virtuelles pour faire travailler votre serveur dès demain ! Et sous peu, vous pourrez vous acheter votre premier serveur.<br />
    En attendant, si vous vous ennuyez, jetez un coup d’oeil aux applications qui vous attendent encore.<br />
    <br /><br />
    <h2><img class="icon" src="design/asterisk.png" />Serveurs</h2><br />

    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Bases sur les serveurs</h3><br />

    Sur Server Fight, tous les joueurs possèdent un ou plusieurs serveurs, a commencer par localhost.<br /><br />
    Les serveurs ont plusieurs caractéristiques que voici :<br />
    <ul>
      <li><img class="icon" src="design/icons/server_power.png" /><b>Une IP</b> : chaque serveur, excepté localhost, est identifié par une IP unique sur le jeu par exemple 125.46.32.214.
      <li><img class="icon" src="design/icons/server_power.png" /><b>Un temps de travail</b> : 10 heures virtuelles par jour.
      <li><img class="icon" src="design/icons/server_power.png" /><b>Des RAM et des RAM containers</b> : les RAM déterminent l’argent que vous gagnez avec la commande <code>work</code> et la probabilité que vous réussissiez à pirater un serveur. Vous pouvez augmenter la sécurité de votre serveur toutes les 100 RAM. Les RAM container permettent de placer 100 RAM de plus sur votre serveur.</li>
      <li><img class="icon" src="design/icons/server_power.png" /><b>Un code numérique</b>, d’une longueur initiale de 6 caractères qui définit la sécurité du serveur (voir plus loin) qui vous permet de vous connecter directement au serveur avec <code>connect</code>. Vous seul y avez accès, à la base.</li>
      <li><img class="icon" src="design/icons/server_power.png" /><b>Un code crypté</b> composé de lettres, de la même taille que le code et accessible a tout le monde. Cependant il doit être décrypté car ne permet pas de se connecter directement au serveur.</li>
    </ul>
    
    <h3>Particularités de localhost</h3><br />

    <ul>
      <li>Invulnérable : ne peut être piraté par d’autres joueurs. Par ailleurs, il ne possède pas de code, est accessible par vous et seulement vous</li>
      <li>Limité à 200 RAM</li>
      <li>Ne permet pas de pirater ni de récupérer les codes cryptés des serveurs d’autres joueurs</li>
    </ul>
    
    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Exploiter un serveur </h3><br />

    Pour tirer profit de vos serveurs, vous pouvez utiliser une partie (ou la totalité) du temps de travail du serveur pour le faire “travailler” avec la commande <code>work</code>. Vous serez alors rémunéré en tokens selon :
    <ul>
      <li>Votre niveau (facteur favorisant)</li>
      <li>Le nombre de RAM installées sur votre serveur (facteur très favorisant)</li>
      <li>Le nombre total de serveurs que vous possédez (facteur défavorisant)</li>
    </ul>

    Rappel de la commande : work [temps de travil]. Requiert d’être connecté à un serveur.<br />
    <br />
    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Le piratage in-game, de A à Z !</h3><br />

    Vous avez un vrai serveur avec une IP ? Vous avez téléchargé ServerCracker (“download servercracker”) ?<br />
    Il est temps d’apprendre les bases du piratage dans ServerFight ! Nous allons voir comment vous protéger, pirater les autres joueurs, et ce qu’il faut savoir à ce sujet.<br />
    <br />
    <u>A savoir : vous ne pouvez pas vous attaquer aux personnes ayant un Security level (moyenne des longueurs des codes de vos serveurs) trop éloigné du vôtre.</u><br />
    <br />
    <b>Attaquons !</b>
    <ol>
      <li><img class="icon" src="design/icons/star.png" />Se connecter a votre serveur.</li>
      <li><img class="icon" src="design/icons/star.png" />Trouver des cibles. La commande “servrandom” vous donnera quelques serveurs qui pourraient être intéressants.</li>
      <li><img class="icon" src="design/icons/star.png" />Choisir une cible <img class="smiley" src="design/smiley/cretin.png" /> Copiez l’IP et obtenez des infos sur le serveur avec “ping [ip]”</li>
      <li><img class="icon" src="design/icons/star.png" />Vous avez votre cible, mouhahahah ! Pour 5 heures de travail, récupérez le code crypté du serveur cible : “infiltrate [ip]”. Le code crypté est un code auquel tout le monde a accès. De la même longueur que le code numérique qui définit la sécurité d’un serveur et la difficulté à le pirater, sauf que ce ne sont pas des chiffres mais des lettres qui le composent.</li>
      <li><img class="icon" src="design/icons/star.png" />Le code crypté a été rangé dans l’application ServerCracker ! Retrouvez le à tout moment, mais sachez que le propriétaire du serveur peut le changer :)</li>
      <li><img class="icon" src="design/icons/star.png" />Maintenant il va falloir décrypter le code du serveur. <br />Ce n’est pas là une chose facile ni immédiate. <br /> Regardons comment faire : <ul> <li> Utilisez la commande crack [code crypté] [temps de travail] (attention, ne mettez pas l’IP à la place du code crypté). </li> <li> Et sachez que vous avez une chance sur (longueur du code du serveur cible au cube - nombre de RAM de votre serveur) pour une heure de temps de travail alors ne vous attaquez pas à de trop gros poissons :P</li></ul></li> 
      <li><img class="icon" src="design/icons/star.png" />Yes !!! <img class="smiley" src="design/smiley/youpi.png" /> Vous avez le code crypté ! Vous pouvez alors vous connecter directement au serveur cible ! Ne perdez pas une seconde. Déconnectez vous de votre serveur avec “disconnect”.</li>
      <li><img class="icon" src="design/icons/star.png" />Connectez vous au serveur cible grâce à <code>connect [ip du serveur cible] [code numérique]</code></li>
      <li><img class="icon" src="design/icons/star.png" />Changez le mot de passe du serveur avec “changepassword” <img class="smiley" src="design/smiley/cretin.png" /></li>
      <li><img class="icon" src="design/icons/star.png" />Le serveur vous est ajouté à ServerManager. <img class="smiley" src="design/smiley/youpi.png" /> L’ancien propriétaire est prévenu qu’il l’a perdu ! MOUHAHAHA !</li>
    </ol>
    <br />
    Plutôt sympathique vous ne trouvez pas ? <img class="smiley" src="design/smiley/cretin.png" /><br />
    <br /><br />
    <h2><img class="icon" src="design/asterisk.png" />Applications</h2><br />
    <h4><img class="icon" src="design/icons/terminal.png" />Terminal</h4>
    <ul>
      <li>Natif</li>
      <li>La base du jeu, puisque l’on joue par lignes de commandes 50% du temps dans le jeu. Ca vous fait peur ? Rappelez vous que ce n’est que de la simulation et ne croyez pas que vous allez utiliser des commandes compliquées ^^</li>
    </ul>
    <br />
    <h4><img class="icon" src="design/icons/servermanager.png" />ServerManager</h4>
    <ul>
      <li>A télécharger avec “download servermanager”</li>
      <li>Répertorie vos serveurs et leurs caractéristiques. Indispensable pour jouer avec plusieurs serveurs, par exemple pour avoir l’IP et les codes pour se connecter a ses serveurs.</li>
    </ul>
    <br />
    <h4><img class="icon" src="design/icons/messenger.png" />Communauté</h4>
    <ul>
      <li>A télécharger avec “download messenger”</li>
      <li>Un t’chat entre joueurs. Éviter le hors sujet dessus, s’il vous plaît !</li>
    </ul>
    <br />
    <h4><img class="icon" src="design/icons/servercracker.png" />ServerCracker</h4>
    <ul>
      <li>A télécharger avec “download servercracker”</li>
      <li>Répertorie les derniers codes cryptés que vous avez obtenus avec la commande “infiltrate”</li>
    </ul>
    <br />
    <h4><img class="icon" src="design/icons/decryptlab.png" />DecryptLab</h4>
    <ul>
      <li>A télécharger avec “download decryptlab”</li>
      <li>Vous permet de créer ou rejoindre des salles avec une mise de tokens. Au bout de quelques secondes pour attendre de nouvelles personnes dans la salle, un code crypté en rapport avec l’informatique apparaît. Trouvez comment décrypter le code affiché et soyez le premier à retranscrire le mot (remettre des lettres dans l’ordre par exemple). Vous gagnez alors la somme des mises !</li>
    </ul>
    <br />
    … il en reste quelques unes mais leur utilité coule de source, et elles sont fournies avec le jeu directement ;)<br />
    <br /><br />
    <h2><img class="icon" src="design/asterisk.png" />Commandes du jeu</h2><br />
    Les commandes sont sous la forme commande [paramètre 1] [paramètre 2] etc.<br />
    Exemples : <br />
    <code>download messenger</code>. download est la commande et messenger est le premier paramètre.<br />
    <code>buy ram 5</code>. buy est la commande, ram le premier paramètre, 5 le second.<br />
    <br />
    <b><img class="smiley" src="design/smiley/shocked.png" /> Attention, si vous recevez une réponse “commande non reconnue” vous avez peut être besoin de servermanager ou servercracker.</b><br />
    <br />
    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Générales</h3><br />
    <ul>
      <li><code>download [nom application]</code>. Installe sur le bureau du jeu une application. Exemple : download messenger.</li>
      <li><code>buy server</code>. Achète un serveur pour 750 tokens le premier et 5000 chacun des suivants.</li>
      <li><code>buy ram [quantité]</code>. Ajoute une certaine quantité de RAM, pour 100 tokens l’unité, pour le serveur sur lequel vous êtes connecté.</li>
      <li><code>buy ramcontainer</code>. Ajoute pour (5000*Le nombre de RAM container que vous avez déjà sur le serveur) un RAM container qui vous permettra d’installer 100 RAM de plus sur le serveur.</li>
    </ul>
    <h3><img class="icon" src="design/icons/bullet_arrow.png" />Sur les serveurs / pour le piratage</h3><br />
    <ul>
      <li><code>connect localhost</code> ou <code>connect [ip] [code]</code>. ouvre les connexions et vous permet d’utiliser le serveur. Les commandes comme buy ram, work s’appliqueront au serveur auquel vous êtes connecté.</li>
      <li><code>disconnect</code>. Permet de rompre les connexions au serveur pour se connecter a un autre serveur.</li>
      <li><code>work [temps de travail]</code>. Commande détaillée plus haut dans le guide. Vous rémunère en fonction des RAM sur votre serveur et de votre niveau.</li>
      <li><code>ping [ip]</code>. Vous donne des informations sur le serveur dont vous avez donné l’IP</li>
      <li><code>infiltrate [ip]</code>. Récupère, pour 5 heures de travail</li>
      <li><code>crack [code crypté]</code>. Tente de récupérer le code en clair d’un serveur pour vous l’approprier. (Rappel : code alphabétique : code crypté, code numérique : code en clair permettant de se connecter directement à un serveur)</li>
      <li><code>secure</code>. Par palier de 100 RAM, augmente la longueur des codes du serveur pour le rendre moins vulnérable.</li>
      <li><code>changepassword</code>. Modifie le mot de passe crypté et en clair du serveur.</li>
    </ul>
    <br />
    merci pour votre lecture et à l’intérêt que vous portez au jeu ! <img class="smiley" src="design/smiley/bye.png" /> (et repassez ici dès que vous avez besoin d'aide !)
  </div>
  <br />
</div>

<?php
  }
?>