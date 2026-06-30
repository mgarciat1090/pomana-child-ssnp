<?php
function pomana_child_scripts() {
    wp_enqueue_style( 'pomana-parent-style', get_template_directory_uri(). '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'pomana_child_scripts' );

 
/**  * Obtener números de boleto de la base de datos  
 * * @param int $order_id - ID del pedido  
 * * @return array - Array de números de boleto  */ 
function obtener_numeros_boleto($order_id) {     
    global $wpdb;          // Nombre de la tabla de lottery log     
    $tabla = $wpdb->prefix . 'lottery_mt_tickets';          // Query para obtener todos los ticket_number de esta orden     
    $query = $wpdb->prepare("SELECT ticket_number FROM $tabla WHERE orderid = %d ORDER BY ticket_number ASC",         
    $order_id     
    );          
    $resultados = $wpdb->get_results($query);          // Extraer solo los números en un array     
    $numeros = array();     
    if ($resultados) {         
        foreach ($resultados as $resultado) {             
            $numero = str_pad($resultado->ticket_number, 5, '0', STR_PAD_LEFT);
            $numeros[] = $numero;
            }     
        }          
        return $numeros; 
    }

/**  * Mostrar números de boleto en email de confirmación  */ 
add_action('woocommerce_email_after_order_table', 'mostrar_boletos_en_email', 10, 4);  
function mostrar_boletos_en_email($order, $sent_to_admin, $plain_text, $email) {     // Solo para emails de clientes (no admin)     
if ($sent_to_admin) {         
    return;     
}          
$order_id = $order->get_id();     
$numeros = obtener_numeros_boleto($order_id);          // Si hay números de boleto, mostrarlos     
if (!empty($numeros)) {         // Verificar si es email HTML o texto plano         
    if ($plain_text) {             // Formato texto plano             
        echo "\n\n";             
        echo "========================================\n";             
        echo "TUS NÚMEROS DE BOLETO\n";             
        echo "========================================\n";             
        echo implode(", ", $numeros) . "\n";             
        echo "¡Guarda estos números para el sorteo!\n";             
        echo "========================================\n";         
    } else {             // Formato HTML             
    echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; margin: 30px 0; text-align: center; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">';             
    echo '<h2 style="margin: 0 0 20px 0; font-size: 28px; font-weight: bold;">🎟️ Tus Números de Boleto</h2>';             
    echo '<div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; margin: 20px 0;">';             
    echo '<p style="font-size: 16px; margin-bottom: 15px;">Total de boletos: <strong>' . count($numeros) . '</strong></p>';             
    echo '<div style="font-size: 32px; font-weight: bold; letter-spacing: 2px; line-height: 1.5;">';                         // Mostrar números con formato bonito             
    if (count($numeros) <= 10) {                 // Pocos boletos: mostrar todos                 
        echo implode(' • ', $numeros);             
    } else {                 // Muchos boletos: mostrar primeros y últimos                 
        $primeros = array_slice($numeros, 0, 5);                 
        $ultimos = array_slice($numeros, -5);                 
        echo implode(' • ', $primeros);                 
        echo '<br><span style="font-size: 24px;">...</span><br>';                 
        echo implode(' • ', $ultimos);             
    }                          
    echo '</div>';             
    echo '</div>';             
    echo '<p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9; color:white;">✨ ¡Guarda este email para el día del sorteo!</p>';             
    echo '</div>';                          // Lista completa en texto pequeño si hay muchos boletos             
    if (count($numeros) > 10) {                 
        echo '<div style="background: #f9f9f9; padding: 15px; margin: 20px_0; border-radius: 5px; text-align: center;">';                
        echo '<p style="margin: 0_0_10px_0; font-size: 12px; color: #666;"><strong>Lista completa de tus números:</strong></p>';                 
        echo '<p style="margin: 0; font-size: 11px; color: #888; line-height: 1.8;">' . implode(', ', $numeros) . '</p>';                 
        echo '</div>';             
        }         
    }     
} 
}

/**  * Mostrar números de boleto en la página de detalles del pedido  */ 
add_action('woocommerce_order_details_after_order_table', 'mostrar_boletos_en_pedido');  
function mostrar_boletos_en_pedido($order) {     
    $order_id = $order->get_id();     
    $numeros = obtener_numeros_boleto($order_id);          
    if (!empty($numeros)) {         
        echo '<section class="woocommerce-lottery-tickets" style="margin-top: 30px;">';         
        echo '<h2 style="color: #667eea; margin-bottom: 20px;">🎟️ Tus Números de Boleto</h2>';         
        echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center;">';                  
        echo '<p style="font-size: 18px; margin-bottom: 15px;">Has comprado <strong>' . count($numeros) . ' boleto(s)</strong></p>';                  // Mostrar números en formato de tarjetas         
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 20px;">';                  
        foreach ($numeros as $numero) {             
            echo '<div style="';             
            echo 'background: rgba(255,255,255,0.2); ';             
            echo 'border: 2px solid rgba(255,255,255,0.3); ';             
            echo 'border-radius: 8px; ';             
            echo 'padding: 15px 25px; ';             
            echo 'font-size: 24px; ';             
            echo 'font-weight: bold; ';             
            echo 'min-width: 100px; ';             
            echo 'text-align: center; ';             
            echo 'box-shadow: 0 2px 8px rgba(0,0,0,0.1);';             
            echo '">';             
            echo $numero;             
            echo '</div>';         
            }                  
        echo '</div>';         
        echo '<p style="margin-top: 20px; font-size: 14px; opacity: 0.9; color: white;">✨ Guarda estos números para el día del sorteo</p>';         
        echo '</div>';         
        echo '</section>';     
        } 
    }

/**  * Añadir columna de números de boleto en la lista de pedidos del admin  */ 
add_filter('manage_edit-shop_order_columns', 'agregar_columna_boletos_admin');  
function agregar_columna_boletos_admin($columns) {     $new_columns = array();          
foreach ($columns as $key => $column) {         
    $new_columns[$key] = $column;                  // Añadir después de la columna de estado         
    if ($key === 'order_status') {             
        $new_columns['lottery_tickets'] = 'Números de Boleto';         
    }     
}          
return $new_columns; }  /**  * Mostrar los números en la columna  */ 
add_action('manage_shop_order_posts_custom_column', 'mostrar_columna_boletos_admin', 10, 2);  
function mostrar_columna_boletos_admin($column, $post_id) {     
    if ($column === 'lottery_tickets') {         
        $numeros = obtener_numeros_boleto($post_id);                  
        if (!empty($numeros)) {             
            if (count($numeros) <= 5) {                 
                echo implode(', ', $numeros);             
            } else {                 
                echo count($numeros) . ' boletos';                 
                echo '<br><small>' . implode(', ', array_slice($numeros, 0, 3)) . '...</small>';             
            }         
        } else {             
            echo '<span style="color: #999;">—</span>';         
        }     
    } 
}  
/**  * Mostrar en la página de edición del pedido  */ 
add_action('woocommerce_admin_order_data_after_order_details', 'mostrar_boletos_admin_pedido');  
function mostrar_boletos_admin_pedido($order) {     
    $order_id = $order->get_id();     
    $numeros = obtener_numeros_boleto($order_id);          
    if (!empty($numeros)) {         
        echo '<div class="order_data_column" style="clear: both; margin-top: 20px;">';        
        echo '<h3 style="color: #667eea;">🎟️ Números de Boleto (' . count($numeros) . ')</h3>';         
        echo '<p style="background: #f0f0f1; padding: 15px; border-left: 4px solid #667eea; font-family: monospace; font-size: 14px;">';         
        echo implode(', ', $numeros);         
        echo '</p>';         
        echo '</div>';     
    } 
}

add_filter("woocommerce_paypal_payments_basic_checkout_validation_enabled", "__return_true");

add_action('woocommerce_order_status_completed', 'fallback_insert_lottery_tickets', 20);
add_action('woocommerce_order_status_processing', 'fallback_insert_lottery_tickets', 20);


function fallback_insert_lottery_tickets($order_id) {
    global $wpdb;

    // Ver registros en el log para esta orden
    $log_entries = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wc_lottery_log WHERE orderid = %d",
        $order_id
    ));

    if (empty($log_entries)) return;

    // Cuántos tickets ya están insertados para esta orden
    $already_inserted = intval($wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}lottery_mt_tickets 
         WHERE orderid = %d",
        $order_id
    )));

    $total_in_log = count($log_entries);

    // Si ya están todos, no hacer nada
    if ($already_inserted >= $total_in_log) return;

    // Insertar solo los faltantes (saltar los primeros $already_inserted)
    $entries_to_insert = array_slice($log_entries, $already_inserted);

    foreach ($entries_to_insert as $entry) {
        // Generate a unique random ticket number (00001–99999) for this lottery
        $ticket_min   = 1;
        $ticket_max   = 50000;
        $max_attempts = 100;
        $random_ticket = null;

        for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
            $candidate = random_int($ticket_min, $ticket_max);

            // Check if this number already exists for this lottery
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}lottery_mt_tickets
                 WHERE lottery_id = %d AND ticket_number = %d",
                $entry->lottery_id,
                $candidate
            ));

            if (intval($exists) === 0) {
                $random_ticket = $candidate;
                break;
            }
        }

        // Safety: if we couldn't find a unique number after max attempts, skip and log
        if ($random_ticket === null) {
            error_log("[Lottery] Could not assign a unique ticket for order {$entry->orderid}, lottery {$entry->lottery_id} after {$max_attempts} attempts.");
            continue;
        }

        $wpdb->insert(
            $wpdb->prefix . 'lottery_mt_tickets',
            [
                'user_id'       => $entry->userid,
                'orderid'       => $entry->orderid,
                'lottery_id'    => $entry->lottery_id,
                'ticket_number' => $random_ticket,
                'created_at'    => $entry->date,
            ],
            ['%d', '%d', '%d', '%d', '%s']
        );
    }
}

 add_filter('rest_endpoints', function($endpoints) {
    if (isset($endpoints['/wp/v2/users'])) unset($endpoints['/wp/v2/users']);
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});

add_action('wp_head', function() {
    echo "<script>
    (function() {
        var _orig = navigator.registerProtocolHandler;
        navigator.registerProtocolHandler = function() {
            console.warn('[Blocked] registerProtocolHandler called', arguments);
            // no-op
        };
    })();
    </script>";
}, 1); // prioridad 1 = antes que cualquier otro script

add_filter('wp_mail', function($args) {
    error_log('TO: ' . print_r($args['to'], true));
    error_log('SUBJECT: ' . $args['subject']);
    error_log('BODY: ' . $args['message']);
    error_log('HEADERS: ' . print_r($args['headers'], true));
    return $args;
});

add_action('woocommerce_email_before_send', function($email) {
    if ($email->id === 'customer_processing_order') {
        error_log('SUBJECT: ' . $email->get_subject());
        error_log('BODY: ' . $email->get_content());
    }
}, 10, 1);
