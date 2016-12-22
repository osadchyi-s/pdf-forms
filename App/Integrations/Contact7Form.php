<?php

namespace PdfFormsLoader\Integrations;

use PdfFormsLoader\Core\Views;

class Contact7Form extends RelationsChecker implements Integration
{
    protected $plugins = ['contact-form-7/wp-contact-form-7.php'];

    public function InitHooks()
    {
       if (!parent::checker()) {
           return false;
       }

       $this->addHooks();

       return $this;
    }

    protected function addHooks() {
        add_filter( 'wpcf7_editor_panels', [$this, 'addMetaBox'] );
        add_action( 'wpcf7_after_save', [$this, 'saveOption'] );
        add_filter('wpcf7_form_response_output', [$this, 'AuthorWpcf7'], 40,4);
        add_action( 'wpcf7_before_send_mail', [$this, 'saveFillable'] );
        add_filter( 'wpcf7_form_class_attr', [$this, 'classAttr'] );
        $this->resetLogFile();
    }

    public function classAttr( $class ) {

        $class .= ' pdfform-ext-';
        return $class;

    }

    public function saveOption($args) {

        if (!empty($_POST)){
            update_option( 'cf7_mch_'.$args->id(), $_POST['wpcf7-pdfform'] );
        }

    }

    public function saveFillable($obj) {
        $cf7_mch = get_option( 'cf7_mch_'.$obj->id() );

        $submission = WPCF7_Submission::get_instance();

        $logfileEnabled = $cf7_mch['logfileEnabled'];
        $logfileEnabled = ( is_null( $logfileEnabled ) ) ? false : $logfileEnabled;


        if( $cf7_mch ) {
            $subscribe = false;

            $regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';
            $callback = array( &$obj, 'cf7_mch_callback' );

            $email = $this->tagReplace( $regex, $cf7_mch['email'], $submission->get_posted_data() );
            $name = $this->tagReplace( $regex, $cf7_mch['name'], $submission->get_posted_data() );

            $lists = $this->tagReplace( $regex, $cf7_mch['list'], $submission->get_posted_data() );
            $listarr = explode(',',$lists);

            $merge_vars=array('FNAME'=>$name);// *x1

            // *x2
            $parts = explode(" ", $name);
            if(count($parts)>1) { // *x3

                $lastname = array_pop($parts);
                $firstname = implode(" ", $parts);
                $merge_vars=array('FNAME'=>$firstname, 'LNAME'=>$lastname);

            } else { // *x4

                $merge_vars=array('FNAME'=>$name);// *x5

            }


            if( isset($cf7_mch['accept']) && strlen($cf7_mch['accept']) != 0 )
            {
                $accept = $this->tagReplace( $regex, $cf7_mch['accept'], $submission->get_posted_data() );
                if($accept != $cf7_mch['accept'])
                {
                    if(strlen($accept) > 0)
                        $subscribe = true;
                }
            }
            else
            {
                $subscribe = true;
            }

            for($i=1;$i<=20;$i++){

                if( isset($cf7_mch['CustomKey'.$i]) && isset($cf7_mch['CustomValue'.$i]) && strlen(trim($cf7_mch['CustomValue'.$i])) != 0 )
                {
                    $CustomFields[] = array('Key'=>trim($cf7_mch['CustomKey'.$i]), 'Value'=>$this->tagReplace( $regex, trim($cf7_mch['CustomValue'.$i]), $submission->get_posted_data() ) );
                    $NameField=trim($cf7_mch['CustomKey'.$i]);
                    $NameField=strtr($NameField, "[", "");
                    $NameField=strtr($NameField, "]", "");
                    $merge_vars=$merge_vars + array($NameField=>$this->tagReplace( $regex, trim($cf7_mch['CustomValue'.$i]), $submission->get_posted_data() ) );
                }

            }

            if( isset($cf7_mch['confsubs']) && strlen($cf7_mch['confsubs']) != 0 ) {
                $mce_csu = true;
            } else {
                $mce_csu = false;
            }

            if($subscribe && $email != $cf7_mch['email']) {

                if (!class_exists('Mailchimp'))
                {
                    require_once( SPARTAN_MCE_PLUGIN_DIR .'/api/Mailchimp.php');
                }

                $wrap = new Mailchimp($cf7_mch['api']);
                $Mailchimp = new Mailchimp( $cf7_mch['api'] );
                $Mailchimp_Lists = new Mailchimp_Lists($Mailchimp);
                // *x6
                try {

                    foreach($listarr as $listid) {
                        $listid = trim($listarr[0]);
                        $result = $wrap->lists->subscribe($listid,
                            array('email'=>$email),
                            $merge_vars,
                            'html', //*xbh
                            $mce_csu, //*xaw
                            true, //*xxz
                            false, //*xrd
                            false // *xgr
                        );
                    }

                    $mch_debug_logger = new mch_Debug_Logger();
                    $mch_debug_logger->log_mch_debug( 'Email submission: Sent Mail Ok ',1,$logfileEnabled );

                } catch (Exception $e) {

                    $mch_debug_logger = new mch_Debug_Logger();
                    $mch_debug_logger->log_mch_debug( 'Email submission: ' .$e->getMessage(),4,$logfileEnabled );

                }
            }

        }

    }


    public function AuthorWpcf7( $mce_supps, $class, $content, $args ) {

        $cf7_mch_defaults = array();
        $cf7_mch = get_option( 'cf7_mch_'.$args->id(), $cf7_mch_defaults );
        $cfsupp = ( isset( $cf7_mch['cf-supp'] ) ) ? $cf7_mch['cf-supp'] : 0;

        if ( 1 == $cfsupp ) {

            $mce_supps .= mce_referer();
            $mce_supps .= mce_author();

        } else {

            $mce_supps .= mce_referer();
            $mce_supps .= '<!-- Chimpmail extension by Renzo Johnson -->';
        }
        return $mce_supps;

    }

    public function addMetaBox ( $panels ) {
        $new_page = array(
            'PDFForm-extension' => array(
                'title' => __( 'PDFForm', 'contact-form-7' ),
                'callback' => [$this, 'wpcf7AddPdfform']
            )
        );

        $panels = array_merge($panels, $new_page);

        return $panels;
    }


    public function wpcf7AddPdfform($args) {
        $cf7_mch_defaults = array();
        $cf7_mch = get_option( 'cf7_mch_'.$args->id(), $cf7_mch_defaults );

        $host = esc_url_raw( $_SERVER['HTTP_HOST'] );
        $url = $_SERVER['REQUEST_URI'];
        $urlactual = $url;
        //var_dump($cf7_mch['logfileEnabled']);

        ?>

        <div class="metabox-holder">

        <h3>PDFForm Extension v./h3>

        <?php return; ?>

        <div class="mce-main-fields">

            <p class="mail-field">
                <label for="wpcf7-mailchimp-name"><?php echo esc_html( __( 'Subscriber Name:', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-contact-form" class="helping-field" target="_blank" title="get help with Subscriber Name"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
                <input type="text" id="wpcf7-mailchimp-name" name="wpcf7-mailchimp[name]" class="wide" size="70" placeholder="[your-name] <= Make sure this the name of your form field" value="<?php echo (isset ($cf7_mch['name'] ) ) ? esc_attr( $cf7_mch['name'] ) : ''; ?>" />
            </p>


            <p class="mail-field">
                <label for="wpcf7-mailchimp-email"><?php echo esc_html( __( 'Subscriber Email:', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-contact-form" class="helping-field" target="_blank" title="get help with Subscriber Email:"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
                <input type="text" id="wpcf7-mailchimp-email" name="wpcf7-mailchimp[email]" class="wide" size="70" placeholder="[your-email] <= Make sure this the name of your form field" value="<?php echo (isset ( $cf7_mch['email'] ) ) ? esc_attr( $cf7_mch['email'] ) : ''; ?>" />
            </p>


            <p class="mail-field">
                <label for="wpcf7-mailchimp-api"><?php echo esc_html( __( 'MailChimp API Key:', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-api-key" class="helping-field" target="_blank" title="get help with MailChimp API Key"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
                <input type="text" id="wpcf7-mailchimp-api" name="wpcf7-mailchimp[api]" class="wide" size="70" placeholder="6683ef9bdef6755f8fe686ce53bdf73a-us4" value="<?php echo (isset($cf7_mch['api']) ) ? esc_attr( $cf7_mch['api'] ) : ''; ?>" />
            </p>


            <p class="mail-field">
                <label for="wpcf7-mailchimp-list"><?php echo esc_html( __( 'MailChimp List ID:', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-list-id" class="helping-field" target="_blank" title="get help with MailChimp List ID"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
                <input type="text" id="wpcf7-mailchimp-list" name="wpcf7-mailchimp[list]" class="wide" size="70" placeholder="5d4e8a6072" value="<?php echo (isset( $cf7_mch['list']) ) ?  esc_attr( $cf7_mch['list']) : '' ; ?>" />
            </p>


            <div class="cme-container mce-support" style="display:none">

                <p class="mail-field mt0">
                    <label for="wpcf7-mailchimp-accept"><?php echo esc_html( __( 'Required Acceptance Field:', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-opt-in-checkbox" class="helping-field" target="_blank" title="get help with Required Acceptance Field - Opt-in"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label><br />
                    <input type="text" id="wpcf7-mailchimp-accept" name="wpcf7-mailchimp[accept]" class="wide" size="70" placeholder="[opt-in] <= Leave Empty if you are not using the checkbox or read the link above" value="<?php echo (isset($cf7_mch['accept'])) ? $cf7_mch['accept'] : '';?>" />
                </p>

                <p class="mail-field">
                    <input type="checkbox" id="wpcf7-mailchimp-conf-subs" name="wpcf7-mailchimp[confsubs]" value="1"<?php echo ( isset($cf7_mch['confsubs']) ) ? ' checked="checked"' : ''; ?> />
                    <label for="wpcf7-mailchimp-double-opt-in"><b><?php echo esc_html( __( 'Enable Double Opt-in (checked = true)', 'wpcf7' ) ); ?></b>   <a href="<?php echo MCE_URL ?>" class="helping-field" target="_blank" title="get help with Custom Fields"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label>
                </p>


                <p class="mail-field">
                    <input type="checkbox" id="wpcf7-mailchimp-cf-active" name="wpcf7-mailchimp[cfactive]" value="1"<?php echo ( isset($cf7_mch['cfactive']) ) ? ' checked="checked"' : ''; ?> />
                    <label for="wpcf7-mailchimp-cfactive"><?php echo esc_html( __( 'Use Custom Fields', 'wpcf7' ) ); ?>  <a href="<?php echo MCE_URL ?>/mailchimp-custom-fields" class="helping-field" target="_blank" title="get help with Custom Fields"> Help <span class="red-icon dashicons dashicons-sos"></span></a></label>
                </p>


                <div class="mailchimp-custom-fields">
                    <?php for($i=1;$i<=10;$i++){ ?>

                        <div class="col-6">
                            <label for="wpcf7-mailchimp-CustomValue<?php echo $i; ?>"><?php echo esc_html( __( 'Contact Form Value '.$i.':', 'wpcf7' ) ); ?></label><br />
                            <input type="text" id="wpcf7-mailchimp-CustomValue<?php echo $i; ?>" name="wpcf7-mailchimp[CustomValue<?php echo $i; ?>]" class="wide" size="70" placeholder="[your-mail-tag]" value="<?php echo (isset( $cf7_mch['CustomValue'.$i]) ) ?  esc_attr( $cf7_mch['CustomValue'.$i] ) : '' ;  ?>" />
                        </div>


                        <div class="col-6">
                            <label for="wpcf7-mailchimp-CustomKey<?php echo $i; ?>"><?php echo esc_html( __( 'MailChimp Custom Field Name '.$i.':', 'wpcf7' ) ); ?></label><br />
                            <input type="text" id="wpcf7-mailchimp-CustomKey<?php echo $i; ?>" name="wpcf7-mailchimp[CustomKey<?php echo $i; ?>]" class="wide" size="70" placeholder="EXAMPLE" value="<?php echo (isset( $cf7_mch['CustomKey'.$i]) ) ?  esc_attr( $cf7_mch['CustomKey'.$i] ) : '' ;  ?>" />
                        </div>

                    <?php } ?>

                </div>


                <p class="mail-field">
                    <input type="checkbox" id="wpcf7-mailchimp-cf-support" name="wpcf7-mailchimp[cf-supp]" value="1"<?php echo ( isset($cf7_mch['cf-supp']) ) ? ' checked="checked"' : ''; ?> />
                    <label for="wpcf7-mailchimp-cfactive"><?php echo esc_html( __( 'Show Developer Backlink', 'wpcf7' ) ); ?> <small>( If checked, a backlink to our site will be shown in the footer. This is not compulsory, but always appreciated <span class="spartan-blue smiles">:)</span> )</small></label>
                </p>


            </div>


            <table class="form-table mt0">
                <tbody>
                <tr>
                    <th scope="row">Debug Logger</th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Debug Logger</span></legend><label for="wpcf7-mailchimp-cfactive">
                                <input type="checkbox"
                                       id="wpcf7-mailchimp-logfileEnabled"
                                       name="wpcf7-mailchimp[logfileEnabled]"
                                       value="1" <?php echo ( isset( $cf7_mch['logfileEnabled'] ) ) ? ' checked="checked"' : ''; ?>
                                />
                                Enable to troubleshoot issues with the extension.</label>
                        </fieldset>
                        <p class="description s-small">- View debug log file by clicking <a href="<?php echo esc_textarea( SPARTAN_MCE_PLUGIN_URL ). '/logs/log.txt'; ?>" target="_blank">here</a>. <br />- Reset debug log file by clicking <a href="<?php echo esc_textarea( $urlactual ). '&mce_reset_log=1'; ?>" target="_blank">here</a>.</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="p-author"><a type="button" aria-expanded="false" class="mce-trigger a-support ">Show advanced settings</a></p>

            <!-- <hr class="p-hr"> -->

            <div class="dev-cta">
                <p><span alt="f488" class="dashicons dashicons-megaphone red-icon"> </span> Hello. My name is Renzo Johnson, I <span alt="f487" class="dashicons dashicons-heart red-icon"> </span> WordPress and I develop this tiny FREE plugin to help users like you. I drink copious amounts of coffee to keep me running longer <span alt="f487" class="dashicons dashicons-smiley red-icon"> </span>. If you've found this plugin useful, please consider making a donation.</p>
                <p>Would you like to <a class="button-primary" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NLNDA3PGPMPRN" target="_blank">buy me a coffee?</a></p>
                <!-- <p>I <span alt="f487" class="dashicons dashicons-heart red-icon"> </span> WordPress.</p> -->
            </div>

            <!--  <div class="mce-container">
    <p class="p-author">This <a href="<?php echo MCE_URL ?>" title="This FREE WordPress plugin" alt="This FREE WordPress plugin">FREE WordPress plugin</a> is currently developed in Orlando, Florida by <a href="//renzojohnson.com" target="_blank" title="Front End Developer: Renzo Johnson" alt="Front End Developer: Renzo Johnson">Renzo Johnson</a>. Feel free to contact with your comments or suggestions.</p>
    <p class="p-author"><a type="button" aria-expanded="false" class="mce-trigger a-support ">Show Your Support</a></p>
  </div> -->

        </div>
        <?php

    }

    public function resetLogFile() {
        if ( isset( $_REQUEST['mce_reset_log'] ) ) {

            $mch_debug_logger = new mch_Debug_Logger();

            $mch_debug_logger->reset_mch_log_file( 'log.txt' );
            $mch_debug_logger->reset_mch_log_file( 'log-cron-job.txt' );
            echo '<div id="message" class="updated fade"><p>Debug log files have been reset!</p></div>';
        }
    }


    public function tagReplace($pattern, $subject, $posted_data, $html = false ) {

        if( preg_match($pattern,$subject,$matches) > 0)
        {

            if ( isset( $posted_data[$matches[1]] ) ) {
                $submitted = $posted_data[$matches[1]];

                if ( is_array( $submitted ) )
                    $replaced = join( ', ', $submitted );
                else
                    $replaced = $submitted;

                if ( $html ) {
                    $replaced = strip_tags( $replaced );
                    $replaced = wptexturize( $replaced );
                }

                $replaced = apply_filters( 'wpcf7_mail_tag_replaced', $replaced, $submitted );

                return stripslashes( $replaced );
            }

            if ( $special = apply_filters( 'wpcf7_special_mail_tags', '', $matches[1] ) )
                return $special;

            return $matches[0];
        }
        return $subject;

    }
}

