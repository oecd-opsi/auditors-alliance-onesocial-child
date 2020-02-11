<div class="wp-block-group content-highlights">
<h2>Highlights</h2>
<ul>
<?php
$textarea = block_value('list');
$list_array = explode( '<br />', $textarea );
foreach ($list_array as $item) {
  echo '<li>' . $item . '</li>';
}
?>
</ul>
</div>
