<?php
session_start();
$tabFiles = scandir("images");
$tabScore = array();
if (file_exists("scores.txt")){
	$tabScore = unserialize(file_get_contents("scores.txt"));
}
if (isset($_GET["ajax"])){
	$tabScore[$_POST["prenom"]] = $_POST["score"];
}
file_put_contents("scores.txt",serialize($tabScore));
?>
<html>
<head>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="css/app.css" >
	<link rel="stylesheet" href="css/font-awesome.css" >
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
	<div class="col-sm-offset-0 col-sm-0">
		<div class="container">
			<h1>Connaissez-vous bien vos <?php echo count($tabFiles)-2;?> collègues ?</h1>
			

			<div id="intro">
				<p>
					Indiquez votre nom pour démarrer le quizz.
				</p>
				<input type="text" id="myname" value="" placeholder="votre nom"/>
				<input type="button" onclick="if($('#myname').val()==''){alert('Nom obligatoire');}else{$('#intro').hide();$('#game').show();$('#code').focus();}" value="Démarrer"/>
				<br/><br/>
				
				<?php
				if (count($tabScore)>0){
					arsort($tabScore);
					?>
					Liste des scores:
					<ul>
					<?php
					foreach ($tabScore as $prenom=>$score){
						echo "<li>".$prenom." -> ".$score."</li>";
					}
					?>
					</ul>
				<?php
				}
				?>
			</div>

			<div id="game" style="display:none">
				<div id="bravo" style="display:none" class="alert alert-success alert-dismissible">
					Bravo
				</div>
				<div id="error" style="display:none" class="alert alert-danger  alert-dismissible">
					Raté
				</div>
				<div id="old_image">
				
				</div>
				
				
				<div id="reponse" ></div>
				
				<hr/>
				<div id="form">
					<h2>De qui s'agit-il ci-dessous ?</h2>
					
					<?php
					
					$tabTmp = array();
					$sListeUser = "";
					foreach ($tabFiles as $sFile){
						if ($sFile != ".." and $sFile != "."){
							$sPrenom = str_replace("2","",str_replace(".jpg","",strtolower($sFile)));
							if (!isset($tabTmp[$sPrenom])){
								if ($sListeUser != ""){
									$sListeUser .= ",";
								}
								$sListeUser .= '"'.$sPrenom.'"';
								$tabTmp[$sPrenom] = $sPrenom;
							}
						}
					}
					
					$k = 0;		
					shuffle($tabFiles);
					foreach ($tabFiles as $sFile){
						if ($sFile != ".." and $sFile != "."){
							$sPrenom = str_replace("2","",str_replace(".jpg","",strtolower($sFile)));
							$sPrenom2 = str_replace("-","",$sPrenom);
							$sPrenom3 = str_replace("-"," ",$sPrenom);
							$k++;
							?>
							<p class="photo" id="photo<?php echo $k;?>" data-prenom="<?php echo $sPrenom;?>" data-prenom2="<?php echo $sPrenom2;?>" data-prenom3="<?php echo $sPrenom3;?>">
								<img src="images/<?php echo $sFile;?>" />
							</p>
						<?php
						}
					}
					?>
					
					Vous devez renseigner le prénom (sans faute) de la personne (sans accent):<br/>
					<input type="text" name="code" id="code" onkeypress="if(event.keyCode == 13){goNext()}"/>&nbsp;
					<input type="button" value="Valider" onclick="goNext()" /><br/>				
				</div>
				<H2>Score: &nbsp;<span id="score" >0</span></H2>
				
				<script>
				var iPhoto = 1;
				var iScore = 0;
				$(document).ready(function() {	
					$("#reponse").html("");				
					showPhoto();
					$("#myname").focus();
					
					
					
					var availableName = [
						<?php
						echo $sListeUser;
						?>					  
					];
					$( "#code" ).autocomplete({
					  source: availableName
					});	


					$( "#code" ).autocomplete({
					  select: function( event, ui ) {
						$( "#code" ).val(ui.item.value);
						goNext();
						
						window.setTimeout(function() {
						  $("#code").val("");
						}, 1000);
						
					  }
					});					
				});
				
				function goNext(){
					$(".alert").hide();
					$("#old_image").html($("#photo"+iPhoto).html());
					var sPrenom = $("#photo"+iPhoto).attr("data-prenom");
					var sPrenom2 = $("#photo"+iPhoto).attr("data-prenom2");
					var sPrenom3 = $("#photo"+iPhoto).attr("data-prenom3");
					if (sPrenom == $("#code").val().toLowerCase() || sPrenom2 == $("#code").val().toLowerCase() || sPrenom3 == $("#code").val().toLowerCase()){
						iScore++;
						$("#bravo").show();
						$("#reponse").html("Il s'agissait bien de "+sPrenom);
					}else{
						$("#error").show();
						$("#reponse").html("Il s'agissait de "+sPrenom);
					}
					$("#score").html(iScore+"/"+iPhoto);
					
					iPhoto++;
					showPhoto();
					$("#code").val("");
					$("#code").focus();
					
					if (iPhoto == <?php echo (count($tabFiles)-1);?>){
						 $.post("index.php?ajax", { score: iScore,prenom:$('#myname').val()}, function(data) {
							 alert("C'est fini, il est temps de retourner au travail, maintenant :-)\nJ'espere que ca vous a plus.\nPour voir la liste des scores, rafraichissez la page.");
							$("").post()
							$("#form").html("");
						 });
					}
				}
				
				function showPhoto(){
					$(".photo").hide();
					$("#photo"+iPhoto).show();	
				}
				
				</script>
			</div>
		</div>
	</div>
	
</body>
</html>