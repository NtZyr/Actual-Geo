<?php

function wpmu_geo_redirects_settings() {
    ?>
            <div class="wrap">
    <?php
            if( !is_multisite() ) {
    ?>
                <h1><?php echo __( 'Multisite is required for geo redirects!', 'actual-geo' ); ?></h1>
                <p>
                    <?php echo __( 'You should setting up multisite network for using geo redirect feature. Read <a href="https://codex.wordpress.org/Create_A_Network" target="__blank">this guide</a> to a setting up Wordpress Multisite.', 'actual-geo' ); ?>
                </p>
    <?php
                return false;
            }
    
            $redirects = get_option( 'geo_redirects' );
            
            if( $redirects && is_array( $redirects ) ) {
                $redirect_num = count( $redirects );
            } else {
                $redirect_num = 0;
            }
    
            $country_label = __( 'Country: ', 'actual-geo' );
            $countryRegion_label = __( 'Country Region: ', 'actual-geo' );
            $url_label = __( 'URL: ', 'actual-geo' );
    ?>
                <h1><?php echo __( 'Actual Geo Redirects', 'actual-geo' ); ?></h1>
                <div class="ag__current-position">
                    <h6><?php echo __( 'Your current position: ', 'actual-geo' ); ?></h6>
                    <ul class="current_position_data">
                    </ul>
                </div>
                <div class="ag__control-box">
                    <ul class="control-box__controls">
                        <li><button name="add_geo-redirect"><?php echo __( 'Add geo redirect', 'actual-geo' ); ?></button></li>
                    </ul>
                </div>
                <form method="post" action="options.php">
                    <?php
                        settings_fields( 'wpmu-geo-redirects' );
                        do_settings_sections( 'wpmu-geo-redirects' ); 
                    ?>
    
                    <table class="redirects-table">
                        <thead>
                        <tr><td><h3><?php echo __( 'All geo redirects', 'actual-geo' ); ?></h3></td></tr>
                        </thead>
                        <tbody>
                        <?php 
                            $i = 0; 
                            if( is_array( $redirects ) ) : 
                            foreach( $redirects as $redirect ) : 
                        ?>
                            <tr>
                                <td>
                                    <label><?php echo $country_label; ?></label>
                                    <select class="chosen-select" name="geo_redirects[<?php echo $i; ?>][country]" id="">
                                        <?php 
                                            $countries = json_decode( file_get_contents( 'http://country.io/names.json' ), true );
                                            asort( $countries );
                                            foreach( $countries as $code => $name ) {
                                        ?>
                                            <option <?php echo ( $code == $redirect['country'] ? 'selected' : '' ); ?> value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>
                                    <label><?php echo $countryRegion_label; ?></label>
                                    <input type="text" name="geo_redirects[<?php echo $i; ?>][region]" value="<?php echo $redirect['region']; ?>">
                                </td>
                                <td>
                                    <label><?php echo $url_label; ?></label>
                                    <select name="geo_redirects[<?php echo $i; ?>][url]">
                                    <?php 
                                        $sites = get_sites();
            
                                        foreach( $sites as $site ) {
        ?>
                                            <option <?php echo ( $site->domain == $redirect['url'] ? 'selected' : '' ); ?> value="<?php echo $site->domain; ?>"><?php echo $site->blogname; ?></option>
        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <!-- Reset geo redirects -->
                                <!-- <td><input type="hidden" name="geo_redirects" value=""></td> -->
                            </tr>
                        <?php 
                            $i++; 
                            endforeach;
                            else : ?>
                                <tr><td><?php echo __( 'There is no geo redirect exists', 'actual-geo' ); ?></td></tr>
                            <?php endif;?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <?php submit_button(); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
    <?php
        }