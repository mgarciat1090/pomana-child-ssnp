<?php
function pomana_child_scripts() {
    wp_enqueue_style( 'pomana-parent-style', get_template_directory_uri(). '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'pomana_child_scripts' );

/**
 * Fuerza configuración de WooCommerce Lottery (wpgenie)
 * Basado en los meta keys reales encontrados en wp_postmeta.
 * 
 * Coloca en: functions.php del tema hijo.
 */
add_action( 'woocommerce_process_product_meta', 'mi_rifa_config_real', 20 );

function mi_rifa_config_real( $post_id ) {
    $product_type = isset( $_POST['product-type'] )
        ? sanitize_text_field( $_POST['product-type'] )
        : '';

    if ( 'lottery' !== $product_type ) {
        return;
    }

    // Total de tickets disponibles para vender (stock real de WooCommerce)
    // 50000 - 1500 + 1 = 48501 tickets en el rango
    update_post_meta( $post_id, '_stock', 48500 );
    wc_update_product_stock( $post_id, 48500 );

    // Número de ganadores
    update_post_meta( $post_id, '_lottery_num_winners', 3 );

    // Precio por ticket
    update_post_meta( $post_id, '_lottery_price', 100 );

    // Fechas de la rifa (formato que usa el plugin)
    update_post_meta( $post_id, '_lottery_dates_from', '2026-03-09 00:00' );
    update_post_meta( $post_id, '_lottery_dates_to',   '2026-08-02 16:00' );

    // No permitir múltiples premios al mismo ganador
    update_post_meta( $post_id, '_lottery_multiple_winner_per_user', 'no' );
}

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
    var_dump($numeros);
    die();
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

// Skip cart and go straight to checkout
add_filter( 'woocommerce_add_to_cart_redirect', function() {
    return wc_get_checkout_url();
});

// Optional: skip cart page if someone visits it directly
add_action( 'template_redirect', function() {
    if ( is_cart() ) {
        wp_redirect( wc_get_checkout_url() );
        exit;
    }
});