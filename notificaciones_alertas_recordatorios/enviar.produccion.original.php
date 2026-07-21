<?php
// Importación de clases necesarias de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carga automática de dependencias instaladas con Composer
require '/var/www/notificaciones_script/PHPMayler/vendor/autoload.php';

// Muestra errores (útil para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos y carga de funciones para los formatos de correo
require_once '/var/www/notificaciones_script/baseDeDatos.php';
require_once 'formatos_html.php';

// Conexión a la base de datos MySQL
$cm = new dbMysql($vhost, $vusuario_mysql, $vpass_mysql, $vbase_datos);

// Consulta SQL para obtener clientes y empresas habilitadas con datos relevantes
$vsql = "SELECT DISTINCT c.idcliente, c.razonsocial, c.	emailEnvioFE, e.Notificar, e.NotificarSuspension, c.Documento, e.Suspension, (SELECT eee.IdEmpresa FROM Empresas eee WHERE eee.rut=c.Documento) as IdEmp
         FROM Clientes c 
         INNER JOIN Empresas e ON c.Documento = e.Rut 
         WHERE c.IdEmpresa = 397 
         AND c.Documento IN (SELECT ee.rut FROM Empresas ee WHERE ee.Habilitada='SI') 
         AND c.Documento > 0";

// Ejecuta la consulta principal
if ($co = $cm->consulta($vsql))
{
    $vcontador = 0;

    // Itera sobre cada cliente encontrado
    while ($re = mysqli_fetch_array($co)) 
    {
        echo $re[1] . "<br>"; // Imprime la razón social del cliente

        // Asigna los valores de la fila a variables
        $vrasonsocial     = $re[1];
        $vemail           = $re[2];
        $vnotificar       = $re[3];
        $vnotificarsus    = $re[4];
        $vrut             = $re[5];
        $vsuspension      = $re[6];
        $vmensaje         = '';
        $vfechasuspension = '';
    	$vIdEmpresaCliente= $re[7];

        // Consulta para obtener la primera venta con saldo pendiente
        $vsql = "SELECT Fecha FROM Ventas 
                 WHERE IdCliente = '" . $re[0] . "' 
                 AND Saldo > 0 
                 AND VC = 'V' 
                 AND IdSucursal > 0 
                 AND (Estado = 'CFE Autorizado.' OR Estado = 'CFE Enviado.' OR Estado = 'CFE Firmado.') 
                 AND (IdTipoDoc = 101 OR IdTipoDoc = 111) 
                 ORDER BY Fecha ASC LIMIT 1";

        // Ejecuta la consulta de ventas
        if ($co2 = $cm->consulta($vsql))
        {
            if ($re2 = mysqli_fetch_array($co2))
            {
                $FechaDias = $re2[0];

                if (!empty($FechaDias))
                {
                    $FechaDias = substr($FechaDias, 0, 10); // Extrae solo la parte de la fecha (sin hora)
                    echo "Fecha: " . $FechaDias . "<br>";

                    $hoy = date("Y-m-d"); // Fecha actual

                    // Crea objetos DateTime para calcular la diferencia
                    $fecha1 = DateTime::createFromFormat('Y-m-d', $FechaDias);
                    $fecha2 = DateTime::createFromFormat('Y-m-d', $hoy);

                    // Si hay días definidos para suspensión, se calcula la fecha de suspensión
                    if($vsuspension > 0)
                    {
                        $fecha3 = new DateTime($FechaDias);
                        $fecha3->modify("+$vsuspension days");
                        $vfechasuspension = $fecha3->format('d-m-Y');
                    }

                    // Si ambas fechas son válidas, calcula la diferencia en días
                    if ($fecha1 && $fecha2)
                    {
                        $diff = $fecha1->diff($fecha2);
                        $dias = $diff->days;

                        // Si la fecha actual es anterior a la fecha de la venta, se invierte el signo
                        if ($fecha2 < $fecha1)
                        {
                            $dias = -$dias;
                        }

                        echo "Días: " . $dias . ", Notificarsus: ".$vnotificarsus.", notificar: ".$vnotificar.", Supension: ".$vsuspension."<br><br>";

                        // Configuración y envío del correo
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'mail.dynamica.com.uy';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'notificaciones@dynamica.com.uy';
                            $mail->Password   = 'P.r8K6{%1[+3';
                            $mail->SMTPSecure = 'tls';
                            $mail->Port       = 587;

                            // Configuración de destinatarios
                            $correo = $vemail;
                            $mail->setFrom('notificaciones@dynamica.com.uy', 'Notificaciones Dynamica');
                            $correos = explode(';', $correo); // Separa los correos por punto y coma

							$primero = true;
							foreach ($correos as $c) {
    							$c = trim($c); // Elimina espacios innecesarios
    							if (!empty($c)) {
        							if ($primero) {
            							$mail->addAddress($c, $vrasonsocial); // Primer correo como destinatario principal
            							$primero = false;
        							} else {
            							$mail->addCC($c, $vrasonsocial); // Los demás como copias (CC)
        							}
    							}
							}

                            $mail->addReplyTo('administracion@dynamica.com.uy', 'Administración Dynamica');
                            $mail->addBCC('administracion@dynamica.com.uy', 'Notificaciones Dynamica'); // Copia oculta

                            $mail->isHTML(true);

                            // Selección del mensaje según los días y configuración del cliente
                            if($vnotificar > 0)
                            {
                                // Recordatorio de pago
                                if($dias > 0 && $dias == ($vnotificar - 10))
                                {
                                    $vmensaje = $vmensaje_recordatoriopago;
                                	$mail->Subject = '=?UTF-8?B?' . base64_encode('Recordatorio de Pago') . '?='; // Asunto codificado en UTF-8
                                }

                                // Aviso de deuda
                                if($dias > 0 && $dias == $vnotificar)
                                {
                                    $vmensaje = $vmensaje_deuda;
                                	$mail->Subject = '=?UTF-8?B?' . base64_encode('Aviso de Deuda') . '?='; // Asunto codificado en UTF-8
                                }
                            }

                            if($vnotificarsus > 0)
                            {
                                // Alerta de suspensión
                                if($dias > 0 && $dias == $vnotificarsus)
                                {
                                    $vmensaje = $vmensaje_alertasuspension;
                                	$mail->Subject = '=?UTF-8?B?' . base64_encode('Alerta de Suspensión') . '?='; // Asunto codificado en UTF-8
                                }
                            }

                            if($vsuspension > 0)
                            {
                                // Notificación de suspensión
                                if($dias > 0 && $dias == $vsuspension)
                                {
                                    $vmensaje = $vmensaje_notificacionsuspension;
                                	$mail->Subject = '=?UTF-8?B?' . base64_encode('Notificación de Suspensión') . '?='; // Asunto codificado en UTF-8
                                }
                            }
                        
                        	$mail->CharSet = "UTF-8";

                            // Si hay mensaje definido, se reemplazan variables y se envía
                            if(!empty($vmensaje))
                            {
                                $vmensaje = str_replace('vRazonSocial', $vrasonsocial, $vmensaje);
                                $vmensaje = str_replace('vRut', $vrut, $vmensaje);
                                $vmensaje = str_replace('vFechaSuspension', $vfechasuspension, $vmensaje);
                                $mail->Body = $vmensaje;

                                $mail->send(); // Envío del correo
                                echo "✅ Correo enviado a: $correo <br>";
                            }

                        }
                        catch (Exception $e)
                        {
                            echo "❌ Error al enviar a $correo: " . $mail->ErrorInfo . "<br>";
                        }
                    
                    	//si pasa a los dísa de suspención + 30 días hacemos suspención forzosa 22/09/2025 - Leonardo Navarro
                    	//Se reduce a +10 por orden de Sebastian 29/12/2025
                    	
                    	//if( $dias >= ($vsuspension + 10) and $vIdEmpresaCliente > 0)  //este sumaba 10 días para la suspención. Comentado el 2105/2026
                		if( $dias >= $vsuspension and $vIdEmpresaCliente > 0)
                    	{
                    		$vsql = "UPDATE Empresas SET Habilitada = 'SU' WHERE IdEmpresa = '" . $vIdEmpresaCliente . "'";
                        	$cm->consulta($vsql);
                    	}

                    }
                    else
                    {
                        echo "Error: Una de las fechas no es válida. Hoy: $hoy | FechaDias: $FechaDias<br><br>";
                    }
                }
                else
                {
                    echo "No hay fecha registrada.<br><br>";
                }
            }
        }

        $vcontador++; // Cuenta cuántos registros se procesaron
    }
}
?>
