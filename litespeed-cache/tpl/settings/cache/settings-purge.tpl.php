<?php
namespace LiteSpeed ;
defined( 'WPINC' ) || exit ;
?>

<h3 class="litespeed-title-short">
	<?php echo __( 'Purge Settings', 'litespeed-cache' ) ; ?>
	<?php $this->learn_more( 'https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache:lscwp:configuration:purge', false, 'litespeed-learn-more' ) ; ?>
</h3>

<?php $this->cache_disabled_warning() ; ?>

<?php
$option_list = array(
	Conf::O_PURGE_POST_ALL => __( 'All pages', 'litespeed-cache' ),
	Conf::O_PURGE_POST_FRONTPAGE => __( 'Front page', 'litespeed-cache' ),
	Conf::O_PURGE_POST_HOMEPAGE => __( 'Home page', 'litespeed-cache' ),
	Conf::O_PURGE_POST_PAGES => __( 'Pages', 'litespeed-cache' ),

	Conf::O_PURGE_POST_PAGES_WITH_RECENT_POSTS => __( 'All pages with Recent Posts Widget', 'litespeed-cache' ),

	Conf::O_PURGE_POST_AUTHOR => __( 'Author archive', 'litespeed-cache' ),
	Conf::O_PURGE_POST_POSTTYPE => __( 'Post type archive', 'litespeed-cache' ),

	Conf::O_PURGE_POST_YEAR => __( 'Yearly archive', 'litespeed-cache' ),
	Conf::O_PURGE_POST_MONTH => __( 'Monthly archive', 'litespeed-cache' ),
	Conf::O_PURGE_POST_DATE => __( 'Daily archive', 'litespeed-cache' ),

	Conf::O_PURGE_POST_TERM => __( 'Term archive (include category, tag, and tax)', 'litespeed-cache' ),
) ;

// break line at these ids
$break_arr = array(
	Conf::O_PURGE_POST_PAGES,
	Conf::O_PURGE_POST_PAGES_WITH_RECENT_POSTS,
	Conf::O_PURGE_POST_POSTTYPE,
	Conf::O_PURGE_POST_DATE,
) ;

?>

<table class="wp-list-table striped litespeed-table"><tbody>

	<?php if ( ! is_multisite() ) : ?>
		<?php require LSCWP_DIR . 'tpl/settings/cache/settings_inc.purge_on_upgrade.tpl.php' ; ?>
	<?php endif; ?>

	<tr>
		<th>
			<?php $id = Conf::O_PURGE_STALE ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_switch( $id ) ; ?>
			<div class="litespeed-desc">
				<?php echo __( 'Always set stale for Purge operation to reduce the server load peak for the following visits.', 'litespeed-cache' ); ?>
			</div>
		</td>
	</tr>

	<tr>
		<th><?php echo __( 'Auto Purge Rules For Publish/Update', 'litespeed-cache' ) ; ?></th>
		<td>
			<div class="litespeed-callout notice notice-warning inline">
				<h4><?php echo __( 'Note', 'litespeed-cache' ) ; ?></h4>
				<p>
					<?php echo __( 'Select "All" if there are dynamic widgets linked to posts on pages other than the front or home pages.', 'litespeed-cache' ) ; ?><br />
					<?php echo __( 'Other checkboxes will be ignored.', 'litespeed-cache' ) ; ?><br />
					<?php echo __( 'Select only the archive types that are currently used, the others can be left unchecked.', 'litespeed-cache' ) ; ?>
				</p>
			</div>
			<div class="litespeed-top20">
				<div class="litespeed-tick-wrapper">
					<?php
						foreach ( $option_list as $id => $title ) {

							$this->build_checkbox( $id, $title ) ;

							if ( in_array( $id, $break_arr ) ) {
								echo '</div><div class="litespeed-top20">';
							}
						}
					?>
				</div>
			</div>
			<div class="litespeed-desc">
				<?php echo __( 'Select which pages will be automatically purged when posts are published/updated.', 'litespeed-cache' ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Conf::O_PURGE_TIMED_URLS ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_textarea( $id, 80 ) ; ?>
			<div class="litespeed-desc">
				<?php echo sprintf( __( 'The URLs here (one per line) will be purged automatically at the time set in the option "%s".', 'litespeed-cache' ), __( 'Scheduled Purge Time', 'litespeed-cache' ) ) ; ?><br />
				<?php echo sprintf( __( 'Both %1$s and %2$s are acceptable.', 'litespeed-cache' ), '<code>http://www.example.com/path/url.php</code>', '<code>/path/url.php</code>' ) ; ?>
				<?php Doc::one_per_line() ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Conf::O_PURGE_TIMED_URLS_TIME ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_input( $id, null, null, 'time' ) ; ?>
			<div class="litespeed-desc">
				<?php echo sprintf( __( 'Specify the time to purge the "%s" list.', 'litespeed-cache' ), __( 'Scheduled Purge URLs', 'litespeed-cache' ) ) ; ?>
				<?php echo sprintf( __( 'Current server time is %s.', 'litespeed-cache' ), '<code>' . date( 'H:i:s' ) . '</code>' ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Conf::O_PURGE_HOOK_ALL ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_textarea( $id, 50 ) ; ?>
			<?php $this->recommended( $id, true ) ; ?>

			<div class="litespeed-desc">
				<?php echo __( 'A Purge All will be executed when WordPress runs these hooks.', 'litespeed-cache' ) ; ?>
				<?php $this->learn_more( 'https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache:lscwp:configuration:advanced#hooks_to_purge_all' ) ; ?>
			</div>
		</td>
	</tr>


</tbody></table>

