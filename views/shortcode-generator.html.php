<div class="wrap">
   <h1><?php _e('Shortcode generator', 'namaste');?></h1>
   
   <form method="post">
      <h3><?php _e('Generate shortcode for listing lessons in  course or module:', 'namaste');?></h3>
      <p><?php _e('Select course:','namaste');?> <select name="course_id" onchange="NamasteLMSSelectCourse(this.value);">
         <option value=""><?php _e('Dynamic / Current (place shortcode inside the course contents)', 'namaste');?></option>
         <?php foreach($courses as $course):?>
            <option value="<?php echo $course->ID?>" <?php if(!empty($_POST['course_id']) and $_POST['course_id'] == $course->ID) echo 'selected';?>><?php echo stripslashes($course->post_title);?></option>
         <?php endforeach;?>
      </select>
      <?php if($use_modules == 1):?>
         <span id="namasteModules" style='display:<?php echo empty($_POST['course_id']) ? 'none' : 'inline';?>'>        
            <span id="namasteModuleID">
               <?php _e('Select module:', 'namaste');?>
               <select name="namaste_module">
                  <option value=""><?php _e('- No module-', 'namaste');?></option>
                  <?php if(!empty($modules) and count($modules)):
                     foreach($modules as $module):?>
                     <option value="<?php echo $module->ID?>" <?php if(!empty($_POST['namaste_module']) and $_POST['namaste_module'] == $module->ID) echo 'selected';?>><?php 
                        echo stripcslashes($module->post_title);?></option>
                  <?php endforeach; 
                  endif;?>
               </select>
            </span>
         </span>
      <?php endif;?>      
      </p>
      <p><input type="checkbox" name="status" value="1" <?php if(!empty($_POST['status'])) echo 'checked'?> onclick="if(!this.checked && this.form.show_grade.checked) this.form.show_grade.checked=false;if(this.checked) {jQuery('#listTagColumn').hide();} else {jQuery('#listTagColumn').show();}"> <?php _e('Include status column.', 'namaste');?> <br>
      <?php _e('Order by:', 'namaste');?> <select name="orderby">
         <option value=""><?php _e('Default', 'namaste');?></option>
         <option value="post_date" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_date') echo 'selected'?>>post_date</option>
         <option value="post_title" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_title') echo 'selected'?>>post_title</option>
         <option value="post_status" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'post_status') echo 'selected'?>>post_status</option>
         <option value="menu_order" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'menu_order') echo 'selected'?>>menu_order</option>
         <option value="comment_count" <?php if(!empty($_POST['orderby']) and $_POST['orderby'] == 'comment_count') echo 'selected'?>>comment_count</option>
      </select>
      <?php _e('Direction:', 'namaste');?> <select name="dir">
         <option value="ASC"><?php _e('Ascending', 'namaste');?></option>
         <option value="DESC" <?php if(!empty($_POST['dir']) and $_POST['dir'] == 'DESC') echo 'selected'?>><?php _e('Descending', 'namaste');?></option>
      </select></p>
      <p style='display:<?php echo empty($_POST['status']) ? 'block' : 'none';?>' id="listTagColumn"><?php _e('List tag:', 'namaste')?><select name="list_tag">
         <option value="ul"><?php _e('Unnumerated list ("ul")', 'namaste');?></option>
         <option value="ol" <?php if(!empty($_POST['list_tag']) and $_POST['list_tag'] == 'ol') echo 'selected'?>><?php _e('Numerated list ("ol")', 'namaste');?></option>
      </select>
      <p><input type="checkbox" name="show_excerpts" value="1" <?php if(!empty($_POST['show_excerpts'])) echo 'checked'?>> <?php _e('Show post excerpts', 'namaste');?> </p>
      <p><input type="checkbox" name="show_grade" value="1" <?php if(!empty($_POST['show_grade'])) echo 'checked'?> onclick="if(this.checked && !this.form.status.checked) this.checked=false;"> <?php _e('Show lesson grade (requires the include status column to be included)', 'namaste');?> </p>
      <p><input type="submit" name="generate_course_lessons" value="<?php _e('Generate shortcode', 'namaste')?>" class="button button-primary"></p>
      <?php if(!empty($_POST['generate_course_lessons'])):?>
         <p><input type="text" value='[<?php
         echo empty($_POST['namaste_module']) ? 'namaste-course-lessons ' : 'namaste-module-lessons ';
         echo empty($_POST['status']) ? '0 ' : 'status ';
         echo empty($_POST['course_id']) ? '0 ' : (empty($_POST['namaste_module']) ? (int)$_POST['course_id'].' ' : (int)$_POST['namaste_module'].' ');
         if(!empty($_POST['orderby'])): echo esc_attr($_POST['orderby']).' '.esc_attr($_POST['dir']).' ';
         else: echo "0 ASC ";
         endif;
         if(!empty($_POST['list_tag'])) echo esc_attr($_POST['list_tag']).' ';
         if(!empty($_POST['show_excerpts'])) echo 'show_excerpts=1 '; 
         if(!empty($_POST['show_grade'])) echo 'show_grade=1 ';
         ?>]' size="60" readonly="readonly" onclick="this.select();"></p>
      <?php endif;?>
   </form>
</div>

<script type="text/javascript">
function NamasteLMSSelectCourse(courseID) {
   <?php if($use_modules):?>
   data = {'action' : 'namaste_ajax', 'type': 'load_modules', 'course_id': courseID};
   jQuery.post('<?php echo admin_url("admin-ajax.php");?>', data, function(msg){
       jQuery('#namasteModules').show();
      jQuery('#namasteModuleID').html(msg);
   });
   <?php endif;?>
   return true;
}
</script>
