<?php	//网站地图
// site domain; no trailing '/' !
define('SITE_DOMAIN', ly200::get_domain());

// defines database connection data
define('DB_HOST', $c['db_cfg']['host']);
define('DB_USER', $c['db_cfg']['username']);
define('DB_PASSWORD', $c['db_cfg']['password']);
define('DB_DATABASE', $c['db_cfg']['database']);

// defines the number of products for paging 
define('PRODUCTS_PER_PAGE', 2);

// the geo-targeting file name
define('GEO_TARGETING_CSV', 'geo_target_data/GeoIPCountryWhois.csv');
?>