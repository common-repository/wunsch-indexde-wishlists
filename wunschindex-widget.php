<?php
/*
Plugin Name: Wunsch-Index.de Wunschlisten Widget
Plugin URI: http://www.wunsch-index.de/wordpress.html
Description: Bindet die eigene wunsch-index.de Wunschliste auf dem Blog ein.
Version: 0.3
Author: Marko Fritzsche
Author URI: http://www.wunsch-index.de
License: GPL2
*/

add_action('widgets_init', 'wunschindex_load_widgets');
add_shortcode('wishlist', 'wunschindex_load_wishes');


function wunschindex_load_wishes($atts) {
	extract(
		shortcode_atts(
			array(
				'listurl' => 'http://www.wunsch-index.de/wunschliste_von_max-1-aaaaaaaa.htm'
			), 
			$atts
		)
	);
	$uri = preg_replace('/\.htm$/','.dat',$listurl);
	$options = array(
		"http" => array(
			"method" => "GET",
			"user_agent" => $_SERVER["HTTP_USER_AGENT"].' ['.$_SERVER['REMOTE_ADDR'].']',
			"header" => ("Accept-Language: " . $_SERVER["HTTP_ACCEPT_LANGUAGE"])
		)
	);
	$data = @file_get_contents($uri, false, stream_context_create($options));
	$wdata = @unserialize($data);

	/*
	print "<!-- \n";
	print_r($wdata);
	print "\n-->\n";
	*/

	if (count($wdata) > 0) {
		?>
		<div id="wunschindex_list">
			<?php 
			if (isset($wdata['items']) && count($wdata['items']) > 0) { 
				foreach ($wdata['items'] as $item) {
					?>
					<div 
						class="<?php if ($item['reserved'] == false) echo "wunschindex_item_free"; else echo "wunschindex_item_reserved"; ?>"
						style="-webkit-border-radius: 10px;-khtml-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px; border:1px solid #aaaaaa; width:100%;padding:10px;"
					>
						<div class="wunschindex_item_image"	style="width:120px;float:left;height:200px;text-align:center;padding-top:5px;">
							<a href="http://www.wunsch-index.de" target="_blank">
								<img src="http://www.wunsch-index.de/images/shopicon_<?php echo $item['shop']; ?>.png" border="0" alt="Wunschliste" /><br/>
							</a><br/>
							<?php
							if ($item['image'] != '') {
								?>
								<a href="<?php echo $item['link']; ?>" target="_blank">
								<img src="<?php echo $item['image']; ?>" border="0" alt="" />
								</a>
								<?php
							}
							?>
							<br/>
							<span style="font-size:18px;"><?php echo number_format($item['price'],2,',','.'); ?> &euro;</span>
						</div>
						<div class="wunschindex_item_text" style="float:left;width:500px;">
							<span style="color:#999999;text-transform:capitalize;font-size:x-small;padding:0px;"><?php echo htmlentities($item['subtitle']); ?></span><br/>
							<a href="<?php echo $item['link']; ?>" target="_blank" style="text-decoration:none;color:#333333;">
								<b><?php echo htmlentities($item['title']); ?></b>
							</a><br/>
							<br/>
							<?php echo htmlentities($item['description']); ?>
							<br/>
							<?php
							if ($item['note']!='') {
								?>
								<br/>
								<b style="color:#ff0000;">Hinweis/Note:</b>
								<?php echo htmlentities($item['note']); ?>
								<br/>
								<?php
							}
							?>
							<br/>
							<?php 
								if ($item['reserved'] == false) {
								?>
									<a href="<?php echo $wdata['uri']; ?>" target="_blank">Wunsch reservieren</a>
								<?php
								} else {
								?>
									Das Produkt wurde bereits reserviert.
								<?php
								}
								?>
							&nbsp;&nbsp;&nbsp;
							<a href="<?php echo $item['link']; ?>" target="_blank">Zum Produkt</a>
						</div>
						<br style="clear:both;" />
					</div>
					<br style="clear:both;" />
					<?php
				}
			}
			?>
		</div>
		<?php
	} else {
		?>
		<a href="<?php echo $instance['url']; ?>" target="_blank">Hier geht's zur Wunschliste<br/><?php echo $instance['title']; ?></a><?php
	}
}


function wunschindex_load_widgets() {
	register_widget( 'wunschindex_Widget' );
}


class wunschindex_Widget extends WP_Widget {

	function wunschindex_Widget() {
		$widget_ops = array( 
			'classname' => 'wunschindex', 
			'description' => __('Zeigt eine wunsch-index.de Wunschliste als Widget', 'wunschindex') 
		);

		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wunschindex-widget' );

		$this->WP_Widget( 
			'wunschindex-widget', 
			__('Wunsch-Index.de', 'wunschindex'), 
			$widget_ops, 
			$control_ops
		);
	}

	/*
		Funktion zur Frontend-Anzeige
	*/
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		$url = $instance['url'];

		echo $before_widget;

		if ($title != '') {
			echo $before_title . $title . $after_title;
		}

		$uri = preg_replace('/\.htm$/','.dat',$instance['url']);
		$options = array(
			"http" => array(
				"method" => "GET",
				"user_agent" => $_SERVER["HTTP_USER_AGENT"].' ['.$_SERVER['REMOTE_ADDR'].']',
				"header" => ("Accept-Language: " . $_SERVER["HTTP_ACCEPT_LANGUAGE"])
			)
		);
		$data = @file_get_contents($uri, false, stream_context_create($options));
		$wdata = @unserialize($data);

		/*
		print "<!-- \n";
		print_r($wdata);
		print "\n-->\n";
		*/

		if (count($wdata) > 0) {
			?>
			<div id="wunschindex_intro" <?php if ($instance['expand'] == true) print 'style="display:none;"'; ?>>
				Aktuell sind noch <b><?php echo $wdata['items_free'] ?></b> nicht erf&uuml;llte W&uuml;nsche auf der Wunschliste:<br/>
				<a href="<?php echo $instance['url']; ?>" target="_blank"><?php echo $instance['title']; ?></a><br/>
				<?php
				if ($wdata['items_reserved'] > 0) {
					?>
					<b><?php echo $wdata['items_reserved']; ?></b> W&uuml;nsche wurden auf der Wunschliste bereits reserviert.
					<?php
				} else {
					?>
					Bisher wurden noch keine W&uuml;nsche reserviert.
					<?php
				}
				?>
				<div style="text-align:right;width:100%"><a href="javascript:document.getElementById('wunschindex_list').style.display='block';document.getElementById('wunschindex_intro').style.display='none';void(0);">[Wunschliste ausklappen]</a></div>
			</div>
			<div id="wunschindex_list" <?php if ($instance['expand'] == false) print 'style="display:none;"'; ?>>
				<?php 
				if (isset($wdata['items']) && count($wdata['items']) > 0) { 
					?>
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
					<?php
					foreach ($wdata['items'] as $item) {
						if ($item['reserved'] == false) {
							?>
							<tr>
								<td style="padding:0px;">
									<?php
									if ($item['image'] != '') {
										if ($instance['linknote'] != '') {
											?>
											<a href="<?php echo $item['link']; ?>" onclick="if (window.confirm('<?php echo str_replace("'","\\'",$instance['linknote']); ?>')) return true; else return false;" target="_blank">
											<img src="<?php echo $item['image']; ?>" border="0" alt="" />
											</a>
											<?php
										} else {
											?>
											<a href="<?php echo $item['link']; ?>" target="_blank">
											<img src="<?php echo $item['image']; ?>" border="0" alt="" />
											</a>
											<?php
										}
									}
									?>	
								</td>
								<td>&nbsp;&nbsp;</td>
								<td style="color:#333333;font-size:x-small;padding:0px;vertical-align:top;">
									<span style="color:#999999;text-transform:capitalize;font-size:x-small;padding:0px;"><?php echo htmlentities($item['subtitle']); ?></span><br/>
									<a href="<?php echo $wdata['uri']; ?>" target="_blank">Wunsch reservieren</a><br/>
									<?php
									if ($instance['linknote'] != '') {
										?>
										<a href="<?php echo $item['link']; ?>" onclick="if (window.confirm('<?php echo str_replace("'","\\'",$instance['linknote']); ?>')) return true; else return false;" target="_blank">Zum Produkt</a><br/>
										<?php
									} else {
										?>
										<a href="<?php echo $item['link']; ?>" target="_blank">Zum Produkt</a><br/>
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="color:#333333;font-size:x-small;padding:0px;vertical-align:top;">
									<?php
									if ($instance['linknote'] != '') {
										?>
										<a href="<?php echo $item['link']; ?>" onclick="if (window.confirm('<?php echo str_replace("'","\\'",$instance['linknote']); ?>')) return true; else return false;" target="_blank" style="text-decoration:none;color:#333333;">
											<?php echo htmlentities($item['title']); ?>
										</a>
										<?php
									} else {
										?>
										<a href="<?php echo $item['link']; ?>" target="_blank" style="text-decoration:none;color:#333333;">
											<?php echo htmlentities($item['title']); ?>
										</a>
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="height:10px;"></td>
							</tr>
							<?php
						}
					}
					?>
					</table>
				<?php
				}
				?>
				<div style="text-align:right;width:100%"><a href="javascript:document.getElementById('wunschindex_list').style.display='none';document.getElementById('wunschindex_intro').style.display='block';void(0);">[Wunschliste zuklappen]</a></div>
			</div>
			<?php
		} else {
			?>
			<a href="<?php echo $instance['url']; ?>" target="_blank">Hier geht's zur Wunschliste<br/><?php echo $instance['title']; ?></a><?php
		}

		?>
		
		<?php

		echo $after_widget;
	}


	/*
		Funktion wird beim Speichern der Settings aufgerufen,
		Fuehre Cache-Refresh durch 
	*/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );
		$instance['linknote'] = strip_tags( $new_instance['linknote'] );
		$instance['expand'] = $new_instance['expand']=='y' ? true : false;

		// TODO: Cache refresh
		
		return $instance;
	}


	function form( $instance ) {

		$defaults = array( 
			'url' => __('http://www.wunsch-index.de', 'wunschindex'),
			'title' => __('Meine Wunschliste', 'wunschindex'),
			'linknote' => __('Bitte nicht vergessen, den Wunsch vor dem Kauf zu reservieren.', 'wunschindex'),
			'expand' => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Titel:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e('URL zur Wunschliste:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" value="<?php echo $instance['url']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'linknote' ); ?>">Text f&uuml;r Popup bei Klick auf &quot;Zum Produkt&quot;:</label>
			<input id="<?php echo $this->get_field_id( 'linknote' ); ?>" name="<?php echo $this->get_field_name( 'linknote' ); ?>" value="<?php echo $instance['linknote']; ?>" style="width:100%;" /><br/>
			<small>Leer lassen zum deaktivieren</small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'expand' ); ?>"><?php _e('Wunschliste immer ausklappen?', 'hybrid'); ?></label><br/>
			<input type="radio" id="<?php echo $this->get_field_id( 'expand' ); ?>" name="<?php echo $this->get_field_name( 'expand' ); ?>" value="y" <?php if($instance['expand'] == true) echo 'checked="checked"'; ?> /> Ja<br/>
			<input type="radio" id="<?php echo $this->get_field_id( 'expand' ); ?>" name="<?php echo $this->get_field_name( 'expand' ); ?>" value="n" <?php if($instance['expand'] == false) echo 'checked="checked"'; ?> /> Nein<br/>
		</p>
		
		<?php
	}
}

?>