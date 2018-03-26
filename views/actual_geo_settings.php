<?php

function actual_geo_settings() {
    $allowed_posttypes = get_option( 'posttypes' );

    $allowed_countries = get_option( 'countries' );
    $default_country = get_option( 'default-country' );
    $upd_country = get_option( 'upd-country' );
    
    $allow_userPlace = get_option( 'user-place' );
    
    $posttypes = get_post_types(
        array(
            'publicly_queryable' => true
        ),
        'objects'
    );

    $countries = json_decode( file_get_contents( 'http://country.io/names.json' ), true );
    asort( $countries );
?>
    <div class="wrap">
        <h1><?php echo __( 'Actual Geo Settings', 'actual-geo' ); ?></h1>
        <form method="post" action="options.php">
            <?php
                settings_fields( 'actual-geo-settings' );
                do_settings_sections( 'actual-geo-settings' ); 

                if( $upd_country == 'yes' ) {
                    foreach( $allowed_posttypes as $posttype ) {
                        $args = array(
                            'post_type' => $posttype,
                            'posts_per_page' => -1
                        );

                        $posts = get_posts( $args );

                        foreach( $posts as $p ) {
                            update_post_meta( $p->ID, 'ag__country', $default_country );
                        }

                    }
                }

                update_option( 'upd-country', null );
            ?>
            
            <table class="global__settings">
                <tbody>
                    <tr>
                        <td>
                            <h3><?php echo __( 'Post Types Settings', 'actual-geo' ); ?></h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo __( 'Allowed post types: ', 'actual-geo' ); ?>
                        </td>
                        <td>
                            <?php foreach( $posttypes as $type ) : ?>
                                <label>
                                    <?php echo $type->label; ?>
                                    <input <?php echo ( $allowed_posttypes && in_array( $type->name, $allowed_posttypes ) ? 'checked' : '' ); ?> type="checkbox" name="posttypes[]" value="<?php echo $type->name; ?>">
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h3><?php echo __( 'Countries & Regions Settings', 'actual-geo' ); ?></h3>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __( 'Allowed countries: ', 'actual-geo' ); ?></td>
                        <td>
                            <select name="countries[]" class="chosen-select" multiple>
                                <?php foreach( $countries as $code => $name ) : ?>
                                    <option <?php echo ( $allowed_countries && in_array( $code, $allowed_countries ) ? 'selected' : '' ); ?> value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php if( $allowed_countries ) : ?>
                    <tr>
                        <td><?php echo __( 'Default Country: ', 'actual-geo' ); ?></td>
                        <td>
                            <select name="default-country" class="chosen-select">
                                <?php foreach( $countries as $code => $name ) : ?>
                                    <?php if( in_array( $code, $allowed_countries ) ) : ?>
                                        <option <?php echo ( $default_country == $code ? 'selected' : '' ); ?> value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if( $allowed_posttypes && $default_country ) : ?>
                    <tr>
                        <td><strong><?php echo __( 'Allow default country for all posts in allowed post types?', 'actual-geo' ); ?></strong></td>
                        <td><input type="checkbox" <?php echo ( $upd_country == 'yes' ? 'checked' : '' ); ?> name="upd-country" value="yes" /></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>
                            <h3><?php echo __( 'User Placement Settings', 'actual-geo' ); ?></h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo __( 'Track user geo position?', 'actual-geo' ); ?>
                        </td>
                        <td>
                            <input <?php echo ( $allow_userPlace == 'yes' ? 'checked' : '' ); ?> type="checkbox" name="user-place" value="yes">
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            <?php submit_button(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php var_dump( $upd_country ); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
<?php
}