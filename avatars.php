<?php
    include dirname(dirname(dirname(dirname(__FILE__))))."/wp-load.php";
    require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
    global $wpdb;

    /* Implementation of data migration for posts from drupal database to wordpress database */

    /* Declare database variable */
    $servername = "localhost";
    $database = "qdtstagingdrupal";
    $username = "qdtstaging";
    $password = "a2F+q4HdvP5=u~O#vcza8_Bj";

    /* Create a new database connection */
    $conn = mysqli_connect($servername, $username, $password, $database);

    /* Check connection */
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn -> set_charset("utf8");
    $i = 0;
    $last_inserted_node = '';
    /* Get node data for a particular type */
    $drupal_node_page_data = mysqli_query($conn, "SELECT * FROM `node` WHERE `type` = 'avatars'  ORDER BY `nid` ASC ");

    while ($node_page_data = mysqli_fetch_assoc($drupal_node_page_data))
    {
        $node_id = $node_page_data['nid'];  
        $vid = $node_page_data['vid'];
        $type = $node_page_data['type'];
        $language = $node_page_data['language'];
        $title = $node_page_data['title'];
        $uid = $node_page_data['uid'];
        $status = $node_page_data['status'];
        $created = $node_page_data['created'];
        $changed = $node_page_data['changed'];
        $comment = $node_page_data['comment'];
        $promote = $node_page_data['promote'];
        $sticky = $node_page_data['sticky'];
        $tnid = $node_page_data['tnid'];
        $translate = $node_page_data['translate'];

        // Convert unix timestamp to datetime
        $created_datetime = date('Y-m-d H:i:s', $created);
        $changed_datetime = date('Y-m-d H:i:s', $changed);

        // Get node revision data
        $drupal_node_revision_data = mysqli_query($conn, "SELECT * FROM `node_revision` WHERE `nid` = '" . $node_id . "'");
        $node_revision_data = mysqli_fetch_assoc($drupal_node_revision_data);

        // Get node access data
        $drupal_node_access_data = mysqli_query($conn, "SELECT * FROM `node_access` WHERE `nid` = '" . $node_id . "'");
        $node_access_data = mysqli_fetch_assoc($drupal_node_access_data);
        
        // Get node type
        $node_type = '';
        $node_name = '';
        $node_description = '';
        $node_orig_type = '';
        $drupal_node_type_data = mysqli_query($conn, "SELECT * FROM `node_type` WHERE `type` = '" . $type . "'");
        $node_type_data = mysqli_fetch_assoc($drupal_node_type_data);
        if (!empty($node_type_data)) {
            $node_type = $node_type_data['type'];
            $node_name = $node_type_data['name'];
            $node_description = $node_type_data['description'];
            $node_orig_type = $node_type_data['orig_type'];
        }

        //node counter data 
        $total_count = 0;
        $daycount = 0;
        $time_stamp = 0;
        $drupal_node_counter_data = mysqli_query($conn, "SELECT * FROM `node_counter` WHERE `nid`= '" . $node_id . "'");
        $node_counter_data = mysqli_fetch_assoc($drupal_node_counter_data);
        if (!empty($node_counter_data)) {
            $total_count = $node_counter_data['totalcount'];
            $daycount = $node_counter_data['daycount'];
            $time_stamp = $node_counter_data['timestamp'];
        }

        // start from here 
        // Get node field_data_field_avatar
        $drupal_field_data_field_avatar = mysqli_query($conn, "SELECT * FROM `field_data_field_avatar` WHERE `entity_id` =  '". $node_id ."'");
        $node_title = mysqli_fetch_assoc($drupal_field_data_field_avatar);
        $node_title_content = '';
        if (!empty($node_title))
        {
            $node_title_content = $node_title['field_title_opengraph_value'];
        }

        // Get node field_data_field_tags
        $drupal_field_data_field_tags = mysqli_query($conn, "SELECT * FROM `field_data_field_tags` WHERE `entity_id` =  '". $node_id ."'");
        $node_title = mysqli_fetch_assoc($drupal_field_data_field_tags);
        $field_tags_tid = '';
        if (!empty($field_data_field_tags)){
            $field_tags_tid = $field_data_field_tags['field_tags_tid'];
        }

        //Get field_data_field_article_type
        $druple_field_data_field_article_type =  mysqli_query($conn, "SELECT * FROM `field_data_field_article_type` WHERE `entity_id` =  '". $node_id ."'");
        $field_data_field_article_type = mysqli_fetch_assoc($drupal_field_data_field_article_type);
        $field_article_type_tid = '';
        if(!empty($field_data_field_article_type)) {
            $field_article_type_tid = $field_data_field_article_type['field_article_type_tid'];
        }

         //Get field_data_field_release_date
         $druple_field_data_field_release_date =  mysqli_query($conn, "SELECT * FROM `field_data_field_release_date` WHERE `entity_id` =  '". $node_id ."'");
         $field_data_field_release_date = mysqli_fetch_assoc($drupal_field_data_field_release_date);
         $field_release_date_value = '';
         if(!empty($field_data_field_release_date)) {
            $field_release_date_value = $field_data_field_release_date['field_release_date_value'];
         }

        // Get the field news  of the node  
        $druple_field_news =  mysqli_query($conn, "SELECT * FROM `field_data_field_news` WHERE `entity_id` =  '". $node_id ."'");
        $node_field_news = mysqli_fetch_assoc($druple_field_news);
        $field_news = '';
        if(!empty($node_field_news )) {
           $field_news = $node_field_news['field_news_value'];
        }

        // Get the   field_recommended_podcasts of the node
        $druple_field_recommended_podcasts =  mysqli_query($conn, "SELECT * FROM `field_data_field_recommended_podcasts
        ` WHERE `entity_id` =  '". $node_id ."'");
        $node_field_recommended_podcasts = mysqli_fetch_assoc($druple_field_recommended_podcasts);
        $field_recommended_podcasts_value = '';
        if(!empty($node_field_recommended_podcasts)) {
           $field_recommended_podcasts_value = $node_field_recommended_podcasts['field_recommended_podcasts_value'];
        }

        // Get the    field_intro_text of the node
        $druple_field_intro_text =  mysqli_query($conn, "SELECT * FROM `field_data_field_intro_text
        ` WHERE `entity_id` =  '". $node_id ."'");
        $node_field_intro_text = mysqli_fetch_assoc($druple_field_intro_text);
        $field_intro_text_value = '';
        $field_intro_text_format='';
        if(!empty($node_field_intro_text)) {
           $field_intro_text_value = $node_field_intro_text['field_intro_text_value'];
           $field_intro_text_format = $node_field_intro_text['field_intro_text_format'];
        }

        // Get node revision content data
        $body_value = '';
        $body_format = '';
        $drupal_field_data_body = mysqli_query($conn, "SELECT * FROM `field_data_body` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_body = mysqli_fetch_assoc($drupal_field_data_body);
        if (!empty($field_data_body)) {
            $body_value = $field_data_body['body_value'];
            $body_format = $field_data_body['body_format'];
        }

        // Get node field_data_field_bottom_line
        $drupal_field_revision_field_bottom_line= mysqli_query($conn, "SELECT * FROM `field_data_field_bottom_line` WHERE `entity_id` = '" . $node_id . "'");
        $field_revision_field_bottom_line = mysqli_fetch_assoc($drupal_field_revision_field_bottom_line);
        $field_bottom_line_value = "";
        $field_bottom_line_format= "";
        if (!empty($field_revision_field_bottom_line)) {
            $field_bottom_line_value = $field_revision_field_bottom_line['field_bottom_line_value'];
            $field_bottom_line_format = $field_revision_field_bottom_line['field_bottom_line_format'];
        }

        
        // Get node field_revision_field_bottom_line_place
        $drupal_field_revision_field_bottom_line_place= mysqli_query($conn, "SELECT * FROM `field_data_field_bottom_line_place` WHERE `entity_id` = '" . $node_id . "'");
        $field_revision_field_bottom_line_place = mysqli_fetch_assoc($drupal_field_revision_field_bottom_line_place);
        $field_bottom_line_place_value = "";
        if (!empty($field_revision_field_bottom_line_place)) {
            $field_bottom_line_place_value = $field_revision_field_bottom_line_place['field_bottom_line_place_value'];
        }


         // Get node field_revision_field_citation
         $drupal_field_revision_field_citation = mysqli_query($conn, "SELECT * FROM `field_data_field_citation` WHERE `entity_id` = '" . $node_id . "'");
         $field_revision_field_citation = mysqli_fetch_assoc($drupal_field_revision_field_citation);
         $field_citation_value = "";
         $field_citation_revision_id="";
         if (!empty($field_revision_field_citation)) {
             $field_citation_value = $field_revision_field_citation['field_citation_value'];
             $field_citation_revision_id = $field_revision_field_citation['field_citation_revision_id'];
         }

         // Get node field_data_field_additional_reading
        $drupal_field_data_field_additional_reading = mysqli_query($conn, "SELECT * FROM `field_data_field_additional_reading` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_additional_reading = mysqli_fetch_assoc($drupal_field_data_field_additional_reading);
        $field_additional_reading_value = "";
        $field_additional_reading_revision_id="";
        if (!empty($field_data_field_additional_reading)) {
            $field_additional_reading_value = $field_data_field_additional_reading['field_additional_reading_value'];
            $field_additional_reading_revision_id = $field_data_field_additional_reading['field_additional_reading_revision_id'];
        }

        // Get field_data_field_image data
        $image_filename = '';
        $image_uri = '';
        $image_mimetype = '';
        $image_filesize = '';
        $image_type = '';
        $image_datetime = '';
        $image_origin = '';
        $field_images_alt='';
        $field_image_fid ='';
        $field_images_title='';
        $field_images_width='';
        $field_images_height= '';
        $image_timestamp = '';
        $drupal_data_field_image = mysqli_query($conn, "SELECT * FROM `field_data_field_images` WHERE `entity_id` = '" . $node_id . "'");
        $data_field_image = mysqli_fetch_assoc($drupal_data_field_image);
        if (!empty($data_field_image)) {
            $field_image_fid = $data_field_image["field_images_fid"];
            $field_images_alt = $data_field_image["field_images_alt"];
            $field_images_title = $data_field_image["field_images_title"];
            $field_images_width = $data_field_image["field_images_width"];
            $field_images_height = $data_field_image["field_images_height"];

            $drupal_image_data = mysqli_query($conn, "SELECT * FROM `file_managed` WHERE `fid` = '" . $field_image_fid . "'");
            $image_data = mysqli_fetch_assoc($drupal_image_data);
            if (!empty($image_data)) {
                $image_filename = $image_data["filename"];
                $image_uri = $image_data["uri"];
                $image_mimetype = $image_data["filemime"];
                $image_filesize = $image_data["filesize"];
                $image_type = $image_data["type"];
                $image_timestamp = $image_data["timestamp"];
                $image_origin  = $image_data['origname'];
                $image_datetime = date('Y-m-d H:i:s', $image_timestamp);
            }
        }

        // Get node field_data_field_display_main_image
        $drupal_field_data_field_display_main_image = mysqli_query($conn, "SELECT * FROM `field_data_field_display_main_image` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_display_main_image = mysqli_fetch_assoc($drupal_field_data_field_display_main_image);
        $field_display_main_image_value = "";
        if (!empty($field_data_field_display_main_image)) {
            $field_display_main_image_value = $field_data_field_display_main_image['field_display_main_image_value'];   
        }

        // Get node field_data_field_youtube
        $drupal_field_data_field_youtube = mysqli_query($conn, "SELECT * FROM `field_data_field_youtube` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_youtube = mysqli_fetch_assoc($drupal_field_data_field_youtube);
        $field_youtube_fid = "";
        $field_youtube_title = "";
        $field_youtube_data = "";
        if (!empty($field_data_field_youtube)) {
            $field_youtube_data = $field_data_field_youtube['field_youtube_data'];
            $field_youtube_title = $field_data_field_youtube['field_youtube_title'];   
            $field_youtube_fid = $field_data_field_youtube['field_youtube_fid'];          
        }

        // Get node field_revision_field_you_may_also_like
        $drupal_field_revision_field_you_may_also_like = mysqli_query($conn, "SELECT * FROM `field_revision_field_you_may_also_like` WHERE `entity_id` = '" . $node_id . "'");
        $field_revision_field_you_may_also_like = mysqli_fetch_assoc($drupal_field_revision_field_you_may_also_like);
        $field_you_may_also_like_nid = "";
        if (!empty($field_revision_field_you_may_also_like)) {
            $field_you_may_also_like_nid = $field_revision_field_you_may_also_like['field_you_may_also_like_nid'];
        }

        // Get node field_data_field_itunes_podcast_title
        $drupal_field_data_field_itunes_podcast_title = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_title` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_title = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_title);
        $field_itunes_podcast_title_value = "";
        $field_itunes_podcast_title_format = '';
        if (!empty($field_data_field_itunes_podcast_title)) {
            $field_itunes_podcast_title_format = $field_data_field_itunes_podcast_title['field_itunes_podcast_title_format'];
            $field_itunes_podcast_title_value = $field_data_field_itunes_podcast_title['field_itunes_podcast_title_value'];
        }

        // Get node field_data_field_itunes_podcast_subtitle
        $drupal_field_data_field_itunes_podcast_subtitle = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_subtitle` WHERE `revision_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_subtitle = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_subtitle);
        $field_itunes_podcast_subtitle_value = "";                
        $field_itunes_podcast_subtitle_format = "";                
        if (!empty($field_data_field_itunes_podcast_subtitle)) {
            $field_itunes_podcast_subtitle_value = $field_data_field_itunes_podcast_subtitle['field_itunes_podcast_subtitle_value'];
            $field_itunes_podcast_subtitle_format = $field_data_field_itunes_podcast_subtitle['field_itunes_podcast_subtitle_format'];
        }

        // Get node field_data_field_itunes_podcast_episode_typ
        $drupal_field_data_field_itunes_podcast_episode_typ = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_episode_typ` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_episode_typ = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_episode_typ);
        $field_itunes_podcast_episode_typ_value = "";                
        if (!empty($field_data_field_itunes_podcast_episode_typ)) {
            $field_itunes_podcast_episode_typ_value = $field_data_field_itunes_podcast_episode_typ['field_itunes_podcast_episode_typ_value'];
        }

        // Get node field_data_field_itunes_podcast_episode_num
        $drupal_field_data_field_itunes_podcast_episode_num = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_episode_num` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_episode_num = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_episode_num); 
        $field_itunes_podcast_episode_num_value = "";
        if (!empty($field_data_field_itunes_podcast_episode_num)) {
            $field_itunes_podcast_episode_num_value = $field_data_field_itunes_podcast_episode_num['field_itunes_podcast_episode_num_value'];
        }
            
        // Get node field_data_field_itunes_podcast_summary
        $drupal_field_data_field_itunes_podcast_summary = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_summary` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_summary = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_summary);
        $field_itunes_podcast_summary_value = "";
        $field_itunes_podcast_summary_format = "";                
        if (!empty($field_data_field_itunes_podcast_summary)) {
            $field_itunes_podcast_summary_value = $field_data_field_itunes_podcast_summary['field_itunes_podcast_summary_value'];
            $field_itunes_podcast_summary_format = $field_data_field_itunes_podcast_summary['field_itunes_podcast_summary_format'];
        }
                
        // Get node field_data_field_itunes_podcast_image
        $drupal_field_data_field_itunes_podcast_image = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_image` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_image = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_image);
        $field_itunes_podcast_image_fid = "";
        $field_itunes_podcast_image_alt = "";
        $field_itunes_podcast_image_title = "";
        $field_itunes_podcast_image_width = "";
        $field_itunes_podcast_image_height = "";
        if (!empty($field_data_field_itunes_podcast_image)) {
            $field_itunes_podcast_image_fid = $field_data_field_itunes_podcast_image['field_itunes_podcast_image_fid'];
            $field_itunes_podcast_image_alt = $field_data_field_itunes_podcast_image['field_itunes_podcast_image_alt'];
            $field_itunes_podcast_image_title = $field_data_field_itunes_podcast_image['field_itunes_podcast_image_title'];
            $field_itunes_podcast_image_width = $field_data_field_itunes_podcast_image['field_itunes_podcast_image_width'];
            $field_itunes_podcast_image_height = $field_data_field_itunes_podcast_image['field_itunes_podcast_image_height'];
        }

        // Get node field_data_field_audio
        $drupal_field_data_field_audio = mysqli_query($conn, "SELECT * FROM `field_data_field_audio` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_audio = mysqli_fetch_assoc($drupal_field_data_field_audio);
        $field_audio_value = "";
        $field_audio_format = "";
        if (!empty($field_data_field_audio)) {
            $field_audio_value = $field_data_field_audio['field_audio_value'];
            $field_audio_format = $field_data_field_audio['field_audio_format'];
        }

        // Get node field_data_field_itunes_podcast_filesize
        $drupal_field_data_field_itunes_podcast_filesize = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_filesize` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_filesize = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_filesize);
        $field_itunes_podcast_filesize_value = "";
        $field_itunes_podcast_filesize_format = "";
        if (!empty($field_data_field_itunes_podcast_filesize)) {
            $field_itunes_podcast_filesize_value = $field_data_field_itunes_podcast_filesize['field_itunes_podcast_filesize_value'];
            $field_itunes_podcast_filesize_format = $field_data_field_itunes_podcast_filesize['field_itunes_podcast_filesize_format'];
        }

        // Get node field_data_field_itunes_podcast_mime
        $drupal_field_data_field_itunes_podcast_mime = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_mime` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_mime = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_mime);
        $field_itunes_podcast_mime_value = "";
        if (!empty($field_data_field_itunes_podcast_mime)) {
            $field_itunes_podcast_mime_value = $field_data_field_itunes_podcast_mime['field_itunes_podcast_mime_value'];
        }

        // Get node field_data_field_itunes_podcast_duration
        $drupal_field_data_field_itunes_podcast_duration = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_duration` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_duration = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_duration);
        $field_itunes_podcast_duration_value = "";
        $field_itunes_podcast_duration_format = "";
        if (!empty($field_data_field_itunes_podcast_duration)) {
            $field_itunes_podcast_duration_value = $field_data_field_itunes_podcast_duration['field_itunes_podcast_duration_value'];
            $field_itunes_podcast_duration_format = $field_data_field_itunes_podcast_duration['field_itunes_podcast_duration_format'];
        }

        // Get node field_data_field_itunes_podcast_guid
        $drupal_field_data_field_itunes_podcast_guid = mysqli_query($conn, "SELECT * FROM `field_data_field_itunes_podcast_guid` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_guid = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_guid);
        $field_itunes_podcast_guid_value = "";
        $field_itunes_podcast_guid_format = "";
        if (!empty($field_data_field_itunes_podcast_guid)) {
            $field_itunes_podcast_guid_value = $field_data_field_itunes_podcast_guid['field_itunes_podcast_guid_value'];
            $field_itunes_podcast_guid_format = $field_data_field_itunes_podcast_guid['field_itunes_podcast_guid_format'];
        }

        // Get node field_data_field_itunes_podcast_pubdate
        $drupal_field_data_field_itunes_podcast_pubdate = mysqli_query($conn, "SELECT * FROM `field_revision_field_itunes_podcast_pubdate` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_itunes_podcast_pubdate = mysqli_fetch_assoc($drupal_field_data_field_itunes_podcast_pubdate);
        $field_itunes_podcast_pubdate_value = "";
        $field_itunes_podcast_pubdate_timezone = "";
        $field_itunes_podcast_pubdate_offset = "";
        if (!empty($field_data_field_itunes_podcast_pubdate)) {
            $field_itunes_podcast_pubdate_value = $field_data_field_itunes_podcast_pubdate['field_itunes_podcast_pubdate_value'];
            $field_itunes_podcast_pubdate_timezone = $field_data_field_itunes_podcast_pubdate['field_itunes_podcast_pubdate_timezone'];
            $field_itunes_podcast_pubdate_offset = $field_data_field_itunes_podcast_pubdate['field_itunes_podcast_pubdate_offset'];
        }

        // Get node field_data_field_articles_related_producer
        $drupal_field_data_field_articles_related_producer = mysqli_query($conn, "SELECT * FROM `field_data_field_articles_related_producer` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_articles_related_producer = mysqli_fetch_assoc($drupal_field_data_field_articles_related_producer);
        $field_articles_related_producer_uid = "";
        if (!empty($field_data_field_articles_related_producer)) {
            $field_articles_related_producer_uid = $field_data_field_articles_related_producer['field_articles_related_producer_uid'];
        }

        // Get node field_data_field_book
        $drupal_field_data_field_book = mysqli_query($conn, "SELECT * FROM `field_data_field_book` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_book = mysqli_fetch_assoc($drupal_field_data_field_book);
        $field_book_value = "";
        $field_book_revision_id = "";
        if (!empty($field_data_field_book)) {
            $field_book_value = $field_data_field_book['field_book_value'];
            $field_book_revision_id = $field_data_field_book['field_book_revision_id'];
        }

        // Get node field_data_field_image_embed_code
        $drupal_field_data_field_image_embed_code = mysqli_query($conn, "SELECT * FROM `field_data_field_image_embed_code` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_image_embed_code = mysqli_fetch_assoc($drupal_field_data_field_image_embed_code);
        $field_image_embed_code_value = "";
        $field_image_embed_code_format = "";
        if (!empty($field_data_field_image_embed_code)) {
            $field_image_embed_code_value = $field_data_field_image_embed_code['field_image_embed_code_value'];
            $field_image_embed_code_format = $field_data_field_image_embed_code['field_image_embed_code_format'];
        }

        // Get node field_data_field_product_information
        $drupal_field_data_field_product_information = mysqli_query($conn, "SELECT * FROM `field_data_field_product_information` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_product_information = mysqli_fetch_assoc($drupal_field_data_field_product_information);
        $field_product_information_value = "";
        $field_product_information_format = "";
        if (!empty($field_data_field_product_information)) {
            $field_product_information_value = $field_data_field_product_information['field_product_information_value'];
            $field_product_information_format = $field_data_field_product_information['field_product_information_format'];
        }

        // Get node field_data_field_javascript
        $drupal_field_data_field_javascript = mysqli_query($conn, "SELECT * FROM `field_data_field_javascript` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_javascript = mysqli_fetch_assoc($drupal_field_data_field_javascript);
        $field_javascript_value = "";
        $field_javascript_revision_id = "";
        if (!empty($field_data_field_javascript)) {
            $field_javascript_value = $field_data_field_javascript['field_javascript_value'];
            $field_javascript_revision_id = $field_data_field_javascript['field_javascript_revision_id'];
        }

        // Get node field_data_field_insticator_code
        $drupal_field_data_field_insticator_code = mysqli_query($conn, "SELECT * FROM `field_data_field_insticator_code` WHERE `entity_id` = '" . $node_id . "'");
        $field_data_field_insticator_code = mysqli_fetch_assoc($drupal_field_data_field_insticator_code);
        $field_insticator_code_value = "";
        $field_insticator_code_format = "";
        if (!empty($field_data_field_insticator_code)) {
            $field_insticator_code_value = $field_data_field_insticator_code['field_insticator_code_value'];
            $field_insticator_code_format = $field_data_field_insticator_code['field_insticator_code_format'];
        }
     
        $post_status = "draft";
        if ($status) {
            $post_status = "publish";
        }

        $comment_status = "closed";
        if ($comment) {
            $comment_status = "open";
        }

        $ping_status = "open";

        // Condition to check whether node already inserted or not
        // $node_id
        /* Declare database variable */
        $server_name = "localhost";
        $database_name = "qdtstaging";
        $local_username = "qdtstaging";
        $local_password = "a2F+q4HdvP5=u~O#vcza8_Bj";

        /* Create a new database connection */
        $connetion = mysqli_connect($server_name, $local_username, $local_password, $database_name);
        /* Check connection */
        if ($connetion->connect_error) {
            die("Connection failed: " . $connetion->connect_error);
        }

        $post_meta_details = mysqli_query($connetion, "SELECT * FROM `wp_postmeta` WHERE `meta_key` = 'node_id' AND `meta_value` = '".$node_id."'");
        $post_meta_data = mysqli_fetch_assoc($post_meta_details);
        if(!empty($post_meta_data)) {
//             echo "Data already inserted for " .$node_id;
		
        } 
		
		else {

//              echo "created the new post";
            $post_array = array(
                'post_author' => '1',
                'post_date' => $created_datetime,
                'post_date_gmt' => $created_datetime,
                'post_content' => wp_slash($body_value),
                'post_title' => $title,
                'post_excerpt' => '',
                'post_status' => $post_status,
                'post_type' => 'articles',
                'comment_status' => $comment_status,
                'ping_status' => $ping_status,
                'post_password' => '',
                'post_name' => $title,
            );

            // Insert the post into the database
            $post_id = wp_insert_post($post_array);
            $last_inserted_node = $node_id;
            $i++;
			var_dump($post_id);
            

            // Create postmeta data and insert it in postmeta table
            if (!empty($node_id)) {
                update_post_meta( $post_id, 'node_id', wp_slash($node_id) );
            }

            if (!empty($language)) {
                update_post_meta( $post_id, 'locale', wp_slash($language) );
            }

            if (!empty($uid)) {
                update_post_meta( $post_id, 'node_user_id', wp_slash($uid) );
            }

            if (!empty($sticky)) {
                update_post_meta( $post_id, 'sticky', wp_slash($sticky) );
            }

            if (!empty($node_type)) {
                update_post_meta( $post_id, 'node_type', wp_slash($node_type) );
            }

            if (!empty($node_name)) {
                update_post_meta( $post_id, 'node_name', wp_slash($node_name) );
            }

            if (!empty($node_description)) {
                update_post_meta( $post_id, 'node_description', wp_slash($node_description) );
            }

            if (!empty($node_orig_type)) {
                update_post_meta( $post_id, 'node_orig_type', wp_slash($node_orig_type) );
            }

            if (!empty($total_count)) {
                update_post_meta( $post_id, 'total_count', wp_slash($total_count) );
            }

            if (!empty($daycount)) {
                update_post_meta( $post_id, 'daycount', wp_slash($daycount) );
            }

            if (!empty($node_access_data)) {
                update_post_meta( $post_id, 'access_data', wp_slash($node_access_data) );
            }

            if (!empty($node_title_content)) {
                update_post_meta( $post_id, 'title_content', wp_slash($node_title_content) );
            }

            if (!empty($field_tags_tid )) {
                update_post_meta( $post_id, 'field_tags', wp_slash($field_tags_tid) );
            }

            if (!empty($field_article_type_tid)) {
                update_post_meta( $post_id, 'field_article_type', wp_slash($field_article_type_tid) );
            }

            if (!empty($field_news)) {
                update_post_meta( $post_id, 'field_news', wp_slash($field_news) );
            }

            if (!empty($field_recommended_podcasts_value)) {
                update_post_meta( $post_id, 'recommended_podcasts_value', wp_slash($field_recommended_podcasts_value) );
            }

            if (!empty($field_intro_text_value)) {
                update_post_meta( $post_id, 'intro_text_value', wp_slash($field_intro_text_value) );
            }

            if (!empty($field_intro_text_format)) {
                update_post_meta( $post_id, 'intro_text_format', wp_slash($field_intro_text_format) );
            }

            if (!empty($body_value)) {
                update_post_meta( $post_id, 'body_value', wp_slash($body_value) );
            }

            if (!empty($body_format)) {
                update_post_meta( $post_id, 'body_format', wp_slash($body_format) );
            }

            if (!empty($field_bottom_line_value)) {
                update_post_meta( $post_id, 'bottom_line_value', wp_slash($field_bottom_line_value) );
            }

            if (!empty($field_bottom_line_format)) {
                update_post_meta( $post_id, 'bottom_line_format', wp_slash($field_bottom_line_format) );
            }

            if (!empty($field_bottom_line_place_value)) {
                update_post_meta( $post_id, 'bottom_line_place_value', wp_slash($field_bottom_line_place_value) );
            }

            if (!empty($field_citation_value)) {
                update_post_meta( $post_id, 'citation_value', wp_slash($field_citation_value) );
            }

            if (!empty($field_citation_revision_id)) {
                update_post_meta( $post_id, 'citation_revision_id', wp_slash($field_citation_revision_id) );
            }

            if (!empty($field_additional_reading_revision_id)) {
                update_post_meta( $post_id, 'additional_reading_revision_id', wp_slash($field_additional_reading_revision_id) );
            }

            if (!empty($field_additional_reading_value)) {
                update_post_meta( $post_id, 'additional_reading_value', wp_slash($field_additional_reading_value) );
            }

            if (!empty($image_filename)) {
                update_post_meta( $post_id, 'image_filename', wp_slash($image_filename) );
            }

            if (!empty($image_uri)) {
                update_post_meta( $post_id, 'image_uri', wp_slash($image_uri) );
            }

            if (!empty($image_mimetype)) {
                update_post_meta( $post_id, 'image_mimetype', wp_slash($image_mimetype) );
            }

            if (!empty($image_filesize)) {
                update_post_meta( $post_id, 'image_filesize', wp_slash($image_filesize) );
            }

            if (!empty($image_type)) {
                update_post_meta( $post_id, 'image_type', wp_slash($image_type) );
            }

            if (!empty($image_datetime)) {
                update_post_meta( $post_id, 'image_datetime', wp_slash($image_datetime) );
            }

            if (!empty($image_origin)) {
                update_post_meta( $post_id, 'image_origin', wp_slash($image_origin) );
            }

            if (!empty($field_image_fid)) {
                update_post_meta( $post_id, 'image_fid', wp_slash($field_image_fid) );
            }
            
            if (!empty($field_images_alt)) {
                update_post_meta( $post_id, 'images_alt', wp_slash($field_images_alt) );
            }

            if (!empty($field_images_title)) {
                update_post_meta( $post_id, 'images_title', wp_slash($field_images_title) );
            }

            if (!empty($field_images_width)) {
                update_post_meta( $post_id, 'images_width', wp_slash($field_images_width) );
            }

            if (!empty($field_images_height)) {
                update_post_meta( $post_id, 'images_height', wp_slash($field_images_height) );
            }

            if (!empty($field_display_main_image_value)) {
                update_post_meta( $post_id, 'display_main_image_value', wp_slash($field_display_main_image_value) );
            }

            if (!empty($field_youtube_fid)) {
                update_post_meta( $post_id, 'youtube_fid', wp_slash($field_youtube_fid) );
            }

            if (!empty($field_youtube_title)) {
                update_post_meta( $post_id, 'youtube_title', wp_slash($field_youtube_title) );
            }

            if (!empty($field_youtube_data)) {
                update_post_meta( $post_id, 'youtube_data', wp_slash($field_youtube_data) );
            }

            if (!empty($field_you_may_also_like_nid)) {
                update_post_meta( $post_id, 'you_may_also_like_nid', wp_slash($field_you_may_also_like_nid) );
            }

            if (!empty($field_itunes_podcast_title_value)) {
                update_post_meta( $post_id, 'itunes_podcast_title_value', wp_slash($field_itunes_podcast_title_value) );
            }

            if (!empty($field_data_field_itunes_podcast_title)) {
                update_post_meta( $post_id, 'data_field_itunes_podcast_title', wp_slash($field_data_field_itunes_podcast_title) );
            }

            if (!empty($field_itunes_podcast_subtitle_value)) {
                update_post_meta( $post_id, 'itunes_podcast_subtitle_value', wp_slash($field_itunes_podcast_subtitle_value) );
            }

            if (!empty($field_itunes_podcast_subtitle_format)) {
                update_post_meta( $post_id, 'itunes_podcast_subtitle_format', wp_slash($field_itunes_podcast_subtitle_format) );
            }

            if (!empty($field_itunes_podcast_episode_typ_value)) {
                update_post_meta( $post_id, 'itunes_podcast_episode_typ_value', wp_slash($field_itunes_podcast_episode_typ_value) );
            }

            if (!empty($field_itunes_podcast_episode_num_value)) {
                update_post_meta( $post_id, 'itunes_podcast_episode_num_value', wp_slash($field_itunes_podcast_episode_num_value) );
            }

            if (!empty($field_itunes_podcast_summary_value)) {
                update_post_meta( $post_id, 'itunes_podcast_summary_value', wp_slash($field_itunes_podcast_summary_value) );
            }

            if (!empty($field_itunes_podcast_summary_format)) {
                update_post_meta( $post_id, 'itunes_podcast_summary_format', wp_slash($field_itunes_podcast_summary_format) );
            }

            if (!empty($field_itunes_podcast_image_fid)) {
                update_post_meta( $post_id, 'itunes_podcast_image_fid', $field_itunes_podcast_image_fid );
            }

            if (!empty($field_itunes_podcast_image_alt)) {
                update_post_meta( $post_id, 'itunes_podcast_image_alt', $field_itunes_podcast_image_alt );
            }

            if (!empty($field_itunes_podcast_image_title)) {
                update_post_meta( $post_id, 'itunes_podcast_image_title', $field_itunes_podcast_image_title );
            }

            if (!empty($field_itunes_podcast_image_width)) {
                update_post_meta( $post_id, 'itunes_podcast_image_width', $field_itunes_podcast_image_width );
            }

            if (!empty($field_itunes_podcast_image_height)) {
                update_post_meta( $post_id, 'itunes_podcast_image_height', $field_itunes_podcast_image_height );
            }

            if (!empty($field_audio_value)) {
                update_post_meta( $post_id, 'audio_value', $field_audio_value );
            }

            if (!empty($field_audio_format)) {
                update_post_meta( $post_id, 'audio_format', $field_audio_format );
            }

            if (!empty($field_itunes_podcast_filesize_value)) {
                update_post_meta( $post_id, 'itunes_podcast_filesize_value', $field_itunes_podcast_filesize_value );
            }

            if (!empty($field_itunes_podcast_filesize_format)) {
                update_post_meta( $post_id, 'itunes_podcast_filesize_format', $field_itunes_podcast_filesize_format );
            }

            if (!empty($field_itunes_podcast_mime_value)) {
                update_post_meta( $post_id, 'itunes_podcast_mime_value', $field_itunes_podcast_mime_value );
            }

            if (!empty($field_itunes_podcast_duration_value)) {
                update_post_meta( $post_id, 'itunes_podcast_duration_value', $field_itunes_podcast_duration_value );
            }

            if (!empty($field_itunes_podcast_duration_format)) {
                update_post_meta( $post_id, 'itunes_podcast_duration_format', $field_itunes_podcast_duration_format );
            }

            if (!empty($field_itunes_podcast_guid_value)) {
                update_post_meta( $post_id, 'itunes_podcast_guid_value', $field_itunes_podcast_guid_value );
            }

            if (!empty($field_itunes_podcast_guid_format)) {
                update_post_meta( $post_id, 'itunes_podcast_guid_format', $field_itunes_podcast_guid_format );
            }

            if (!empty($field_itunes_podcast_pubdate_value)) {
                update_post_meta( $post_id, 'itunes_podcast_pubdate_value', $field_itunes_podcast_pubdate_value );
            }

            if (!empty($field_itunes_podcast_pubdate_timezone)) {
                update_post_meta( $post_id, 'itunes_podcast_pubdate_timezone', $field_itunes_podcast_pubdate_timezone );
            }

            if (!empty($field_itunes_podcast_pubdate_offset)) {
                update_post_meta( $post_id, 'itunes_podcast_pubdate_offset', $field_itunes_podcast_pubdate_offset );
            }

            if (!empty($field_articles_related_producer_uid)) {
                update_post_meta( $post_id, 'articles_related_producer_uid', $field_articles_related_producer_uid );
            }

            if (!empty($field_book_value)) {
                update_post_meta( $post_id, 'book_value', $field_book_value );
            }

            if (!empty($field_book_revision_id)) {
                update_post_meta( $post_id, 'book_revision_id', $field_book_revision_id );
            }

            if (!empty($field_image_embed_code_value)) {
                update_post_meta( $post_id, 'image_embed_code_value', $field_image_embed_code_value );
            }

            if (!empty($field_image_embed_code_format)) {
                update_post_meta( $post_id, 'image_embed_code_format', $field_image_embed_code_format );
            }

            if (!empty($field_product_information_value)) {
                update_post_meta( $post_id, 'product_information_value', $field_product_information_value );
            }

            if (!empty($field_product_information_format)) {
                update_post_meta( $post_id, 'product_information_format', $field_product_information_format );
            }

            if (!empty($field_javascript_value)) {
                update_post_meta( $post_id, 'javascript_value', $field_javascript_value );
            }

            if (!empty($field_javascript_revision_id)) {
                update_post_meta( $post_id, 'javascript_revision_id', $field_javascript_revision_id );
            }

            if (!empty($field_insticator_code_value)) {
                update_post_meta( $post_id, 'insticator_code_value', $field_insticator_code_value );
            }

            if (!empty($field_insticator_code_format)) {
                update_post_meta( $post_id, 'insticator_code_format', $field_insticator_code_format );
            }

            /* Create a new post type for attachment */
            if (!empty($image_filename)) {
                // Create new post object
                $new_post_array = array(
                    'post_author' => '1',
                    'post_date' => $image_datetime,
                    'post_date_gmt' => $image_datetime,
                    'post_content' => '',
                    'post_title' => $image_filename,
                    'post_excerpt' => '',
                    'post_status' => 'inherit',
                    'comment_status' => $comment_status,
                    'ping_status' => 'closed',
                    'post_name' => $image_filename,
                    'post_modified' => $image_datetime,
                    'post_modified_gmt' => $image_datetime,
                    'post_parent' => $post_id,
                    'post_type' => 'attachment',
                    'post_mime_type' => $image_mimetype,
                    'guid' => site_url() . "/wp-content/uploads/2022/04/" . $image_filename,
                );

                // Insert the post into the database
                $new_post_id = wp_insert_post($new_post_array);

                // Insert postmeta data for the attachment
                if (!empty($new_post_id)) {

                    $image_filename = "2022/04/" . $image_filename;
                    $sql = 'INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES("' . $new_post_id . '", "_wp_attached_file", "' . $image_filename . '")';
                    if (mysqli_query($connetion, $sql)) {
                        echo "Data Inserted successfully.<br>";
                    } else {
                        echo "Data Insertion Failed; ".mysqli_error($phpconnect);
                    }

                    $sql = 'INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES("' . $post_id . '", "_thumbnail_id", "' . $new_post_id . '")';
                    if (mysqli_query($connetion, $sql)) {
                        echo "Data Inserted successfully.<br>";
                    } else {
                        echo "Data Insertion Failed; ".mysqli_error($phpconnect);
                    }
                }
            }
            /* End of attachemnt post type */

            $image_gallery_id_array = [];
            $image_gallery_uri_array = [];
            $image_gallery_filename_array = [];
            $field_gallery_title_array = [];
            $field_gallery_alt_array = [];
            $field_gallery_width_array = [];
            $field_gallery_height_array = [];

            $drupal_field_data_field_spw_image_gallery = mysqli_query($conn, "SELECT * FROM `field_data_field_gallery` WHERE `entity_id` = '" . $node_id . "'");
            while ($field_data_field_spw_image_gallery = mysqli_fetch_assoc($drupal_field_data_field_spw_image_gallery)) {
                $image_gallery_uid = '';
                $image_gallery_filename = '';
                $image_gallery_uri = '';
                $image_gallery_mimetype = '';
                $image_gallery_filesize = '';
                $image_gallery_status = '';
                $image_gallery_datetime = '';
                $field_gallery_fid = $field_data_field_spw_image_gallery["field_gallery_fid"];
                $field_gallery_alt = $field_data_field_spw_image_gallery["field_gallery_alt "];
                $field_gallery_title = $field_data_field_spw_image_gallery["field_gallery_title"];
                $field_gallery_width = $field_data_field_spw_image_gallery["field_gallery_width"];
                $field_gallery_height = $field_data_field_spw_image_gallery["field_gallery_height"];

                $drupal_image_gallery_data = mysqli_query($conn, "SELECT * FROM `file_managed` WHERE `fid` = '" . $field_gallery_fid . "'");
                $spw_image_gallery_data = mysqli_fetch_assoc($drupal_image_gallery_data);
                if (!empty($spw_image_gallery_data)) {
                    $image_gallery_uid = $spw_image_gallery_data["uid"];
                    $image_gallery_filename = $spw_image_gallery_data["filename"];
                    $image_gallery_uri = $spw_image_gallery_data["uri"];
                    $image_gallery_mimetype = $spw_image_gallery_data["filemime"];
                    $image_gallery_filesize = $spw_image_gallery_data["filesize"];
                    $image_gallery_status = $spw_image_gallery_data["status"];
                    $image_gallery_timestamp = $spw_image_gallery_data["timestamp"];
                    $image_gallery_datetime = date('Y-m-d H:i:s', $image_gallery_timestamp);
                    $image_gallery_type = $spw_image_gallery_data['type'];
                    $image_gallery_origname	 = $spw_image_gallery_data['origname'];
                }

                /* Create a new post type for attachment */
                if (!empty($image_gallery_filename)) {
                    // Create new post object
                    $new_post_array = array(
                        'post_author' => '1',
                        'post_date' => $image_gallery_datetime,
                        'post_date_gmt' => $image_gallery_datetime,
                        'post_content' => '',
                        'post_title' => $image_gallery_filename,
                        'post_excerpt' => '',
                        'post_status' => 'inherit',
                        'comment_status' => $comment_status,
                        'ping_status' => 'closed',
                        'post_name' => $image_gallery_filename,
                        'post_modified' => $image_gallery_datetime,
                        'post_modified_gmt' => $image_gallery_datetime,
                        'post_parent' => $post_id,
                        'post_type' => 'attachment',
                        'post_mime_type' => $image_gallery_mimetype,
                        'guid' => site_url() . "/wp-content/uploads/2022/04/" . $image_gallery_filename,
                    );

                    // Insert the post into the database
                    $new_post_id = wp_insert_post($new_post_array);
                    array_push($image_gallery_id_array, $new_post_id);
                    array_push($image_gallery_uri_array, $image_gallery_uri);
                    array_push($image_gallery_filename_array, $image_gallery_filename);
                    array_push($field_gallery_title_array, $field_gallery_title);
                    array_push($field_gallery_alt_array, $field_gallery_alt);
                    array_push($field_gallery_width_array, $field_gallery_width);
                    array_push($field_gallery_height_array, $field_gallery_height);
                }
                /* End of attachemnt post type */
            }
            // Insert postmeta data for the attachment
            if (count($image_gallery_id_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_id', $image_gallery_id_array);
            }

            if (count($image_gallery_uri_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_url', $image_gallery_uri_array);
            }

            if (count($image_gallery_filename_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_name', $image_gallery_filename_array);
            }

            if (count($field_gallery_title_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_title', $field_gallery_title_array);
            }

            if (count($field_gallery_alt_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_alt', $field_gallery_alt_array);
            }

            if (count($field_gallery_width_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_width', $field_gallery_width_array);
            }

            if (count($field_gallery_height_array) > 0) {
                update_post_meta($post_id, '_old_gallery_thumbnail_height', $field_gallery_height_array);
            }
//             End of spw image gallery images //
        }
    }
    print_r("Last Inserted Node ID :- " . $last_inserted_node);
    print_r("Total Inserted Records :- " . $i);
?>
