<div style="margin-top:20px">
	<table class="widefat">
		<thead>
			<tr>
				<th>
					Destination Url
				</th>
				<th>
					Slug
				</th>
				<th>
					Date Added
				</th>
				<th>
					Action
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>
					Destination Url
				</th>
				<th>
					Slug
				</th>
				<th>
					Date Added
				</th>
				<th>
					Action
				</th>
			</tr>
		</tfoot>
		<tbody>
<?php
			foreach($urls as $url) {
?>
				<tr>
					<td>
						<?php echo $url->destination; ?>
					</td>
					<td>
						<?php echo $url->slug; ?>
					</td>
					<td>
						<?php echo $url->time_created; ?>
					</td>
					<td>
						<?php
							echo '<a href="'.site_url().'/?delete-url=true&id='.$url->id.'">Delete</a>';
						?>
					</td>
				</tr>
	
<?php
			} //end foreach
?>
		</tbody>
	</table>
	
</div>