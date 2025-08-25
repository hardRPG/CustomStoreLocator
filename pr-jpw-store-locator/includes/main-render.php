<?php
function jpw_store_locator_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . "jpw_markers";
    $markers = $wpdb->get_results("SELECT * FROM $table_name");

    ob_start();
    ?>
    <div id="jpw-map" style="width: 100%; height: 500px;"></div>
    <script>
      function initMap() {
        const center = { lat: 48.137154, lng: 11.576124 };

        const map = new google.maps.Map(document.getElementById("jpw-map"), {
          zoom: 6,
          center: center,
        });

        const markers = <?php echo json_encode($markers); ?>;

        markers.forEach(function(m) {
          const marker = new google.maps.Marker({
            position: { lat: parseFloat(m.lat), lng: parseFloat(m.lng) },
            map: map,
            title: m.title,
          });

          const infoWindow = new google.maps.InfoWindow({
            content: `<h3>${m.title}</h3><p>${m.description}</p><p>${m.address}</p>`
          });

          marker.addListener("click", () => {
            infoWindow.open(map, marker);
          });
        });
      }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mein_store_locator', 'jpw_store_locator_shortcode');
?>