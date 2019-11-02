<?php
	session_start();
	$fArticulos=null;
	$fichero='php_238_articulos.txt';
	if (!file_exists($fichero)) {
		file_put_contents($fichero, null);
		$tablaCompra = array();
	} else {
		$fArticulos=file_get_contents($fichero);
		//convierte de formato JSON a array  (true para arrays asociativos)
		$tablaCompra=json_decode($fArticulos,true);
	}		
	
	
    

	//Crear array de sesion ($_SESSION)
	if (isset($_SESSION['articulos'])) {
		$tablaComprasession = $_SESSION['articulos'];
	} else {
		$tablaComprasession = array();
	}
	//para ver el contenido de la $_SESSION
	//print_r($_SESSION);
	
	//variables
	$listaCategorias=array('relojes', 'anillos', 'pulseras', 'collares');
	$resultado=null;
	$articulos=null;
	$descripción=$categoria=null;
	$precio=0;
	$combo=null;

	
	
	//completa el combo de la categoria con el array $listaCategorias
	$combocategorias=completarComboCategorias($listaCategorias);

	//1.-Alta Articulos
	if (isset($_POST['alta'])) {
		$categoria=$_POST['categoria'];
		$descripcion=$_POST['descripcion'];
		$precio=$_POST['precio'];
		//echo " --> $categoria $descripcion  $precio";

		if (trim($descripcion)=='' || trim($precio)=='' || !is_numeric($precio)) {
			$resultado="revisar datos entrada incorrectos o no cumplimentados";
		} else {
			//validar si existe el articulo en la lista de la compra
			$id=uniqid();   
			if (existeArticulo($descripcion,$id)) {
				$resultado="articulo ya añadido al carrito de la compra";
			} else {
				$tablaCompra[$id]= array('categoria'=>$categoria, 'descripcion'=>$descripcion, 'precio'=>$precio);
				$resultado='id:'.$id.' alta articulo realizada';

			}
		}	
	}
	//print_r($tablaCompra);
	//3.-baja de un articulo del carrito de la compra 
	if (isset($_POST['bajaArticulo'])) {
		echo "entro en bajaArticulo";
		$indice=$_POST['id'];
		unset($tablaCompra[$indice]);
		$resultado='id:'.$indice.' baja del articulo de la lista de la compra';
	}

	//4.-Modificación lista de la compra
	if (isset($_POST['modificar'])) {
		echo "entro en modificar desde php";
 		$id=$_POST['id'];
 		$categoria=$_POST['categoria'];
 		$descripcion=$_POST['descripcion']; 
 		$precio=$_POST['precio'];
 		if (trim($descripcion)=='' || trim($precio)=='' || !is_numeric($precio)) {
			$resultado="revisar datos entrada incorrectos o no cumplimentados";
		} else {
			//validar si existe el articulo en la lista de la compra
			if (existeArticulo($descripcion,$id)) {
				$resultado="articulo ya añadido al carrito de la compra";
			} else {
				$tablaCompra[$id]= array('categoria'=>$categoria, 'descripcion'=>$descripcion, 'precio'=>$precio);
				$resultado='id:'.$id.' Modificación articulo realizado';
			}
		}	 
	}

	//clasificar contenido de la lista de la compra por descripcion
	//ksort($_SESSION['articulos']);
	// //ordenar el array por primera clave del segundo array (ordenar por categoria)
	//asort($_SESSION['articulos']);
	//multisort (por descripcion)  - extraer valor 
	//creamos un array con las claves del array de articulos (id)
	$claves = array_keys($tablaCompra);
	//creamos un array con el dato (columna) que queremos ordenar (descripcion)
	$descripcion = array_column($tablaCompra, 'descripcion');
	//ordenamos el array de direcciones de forma ascendente y, simultaneamente, se ordenara el array de articulos y el de claves por la misma ordenación de claves que el primero
	array_multisort($descripcion, SORT_ASC, $tablaCompra, $claves);
	//substituimos las claves del array de personas por las del array de claves 
	$tablaCompra = array_combine($claves, $tablaCompra);
	//print_r($tablaCompra);
	//echo "<br>";
	//print_r($_SESSION['articulos']);

	//2.-Mostrar en el navegador la relación de articulos	
	foreach ($tablaCompra as $k => $datosArticulos) {
		$articulos.="<tr>";	
				$articulos.="<td class='id'>$k</td>";
				$combo="<td><select class='categoriaarticulo'>";
				foreach ($listaCategorias as $categoria) {
					if ($datosArticulos['categoria']==$categoria) {
						$combo.="<option class='sel' selected>$categoria</option>";
					} else {
						$combo.="<option>$categoria</option>";
					}
				}
				$combo.="</select></td>";
				$articulos.=$combo;
				$articulos.="<td><input type='text' value='$datosArticulos[descripcion]' class='descripcion'/> </td>";
				$articulos.="<td><input type='number' value='$datosArticulos[precio]' min='1' class='precio'/> </td>";
				$articulos.="<td>";
						$articulos.="<form method='post' action='#'>";
								$articulos.="<input type='hidden' name='id' value='$k'>";
								$articulos.="<input type='submit' name='bajaArticulo' value='baja'>";
						$articulos.="</form>";
						$articulos.="<input type='button' value='modificar' class='modificar'>";
				$articulos.="</td>";	
		$articulos.="</tr>";
	}

	//actualizar la variable de sesion		
	$_SESSION['articulos']=$tablaCompra;
	//actualiza fichero con la lista articulos
	$fArticulos=json_encode($tablaCompra,true);
	file_put_contents($fichero, $fArticulos);


	//FUNCIONES //////////////////////////////////////////////////	
	function completarComboCategorias($lista) {
		$comboI=null;
		foreach ($lista as $id => $valor) {
			$comboI.="<option>$valor</option><br>";
		}
		return $comboI;	
	}
	
	function existeArticulo($descripcion,$id) {
		//buscar el array tablaCompra
		global $tablaCompra;

		foreach ($tablaCompra as $clave => $valor) {
			if (in_array($descripcion, $valor) && $id != $clave) {
				return true;
			}//end if
		}//end foreach
		return false;
	}
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style type="text/css">
		div.container {
			margin: auto; width:920px; text-align: center;
		}
		table {
			border: 5px ridge blue;
			width: 900px;
		}
		th, td {
			background:white; width:auto; border: 2px solid green; text-align: left;
		}
		input[type=text] {width: 200px;}
		input[type=submit] {width: auto;}
	</style>
	<script type="text/javascript" src='https://code.jquery.com/jquery-3.1.1.min.js'></script>
	<script type="text/javascript">
		window.onload = function() {
			//recuperar botones a recuperar
			var botones=document.querySelectorAll('.modificar');
			//activar listener para modificar (tipo de boton y función a lanzar)
			for (i=0; i< botones.length; i++) {
				botones[i].addEventListener('click', modificar);
			}
		}

		function modificar() {
			alert ("modificar desde javascript");
			//situarnos en la etiqueta TR de la fila sobre la que hemos pulsado el boton de modificar
			// opcion 1 >>>>> var tr=this.parentNode.parentNode;
			//opcion 2 >>>>>> closest busca la etiqueta más cercana del tipo que se indique
			
			var tr=this.closest('tr');
			var id=tr.querySelector('.id').innerText;
			//recuperar los datos a partir de la etiqueta TR
			var select=tr.querySelector('.categoriaarticulo');
        	var text = select.options[select.selectedIndex].innerText; //El texto de la opción seleccionada
			alert (text);
			var descripcion=tr.querySelector('.descripcion').value;
			var precio=tr.querySelector('.precio').value;
			//informar el formulario oculto
			document.getElementById('id').value=id;
			document.getElementById('categoria').value=text;

			document.getElementById('descripcion').value=descripcion;
			document.getElementById('precio').value=precio;
			// //enviar formulario al servidor
			document.getElementById('formulario').submit();
		}		
	</script>
</head>
<body>
	<div class="container">
		<h2 style="text-align:center">EJERCICIO ARTICULOS</h2>
		<span><?=$resultado?></span><br><br>
		<form name="formularioalta" method="post" action="#">
			<table border='2'>
				<tr><th>Categoría</th><th>Descripción</th><th>Precio</th><th colspan='2' style='width:150px'>Opción</th></tr>
				<tr>
				<td><select name='categoria'">
					<!--lista de categorias -->
					<?=$combocategorias?>
				</select></td>
				<td><input type='text' size='50' maxlenght='100' name='descripcion' /></td>
				<td><input type='number' maxlenght='5' name='precio' min="1" /></td>
				<td colspan='2'><input type='submit' name='alta' value='Agregar' /></td>
				</tr>
			</table>
		</form><br>
		<form name="formulario" id="formulario" method="post" action="#"> 
			<input type="hidden" name="id" id="id">
			<input type="hidden" name="categoria" id="categoria">
			<input type="hidden" name="descripcion" id="descripcion">
			<input type="hidden" name="precio" id="precio">
			<input type="hidden" name="modificar">
		</form>
		<div>
			<table>
				<?php echo $articulos; ?>
			</table>
		</div>
	</div>
</body>
</html>