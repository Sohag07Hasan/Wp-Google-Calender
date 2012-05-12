<div class="wrap">
	<h2>Insert an Event to the Google calender </h2>
	<p> Enable G calender event <input id="enable_calender_event" type="checkbox" name="gc_enabled" value="1" /></p>
	<table  class="form-table" id="Gcalender_form" style="display: none;">
		<tr>
			<td>Select a calender</td>
			<td colspan="2">
				<select name="gc_id">
					<?php
						if(empty($calList)){
							echo "<option>No calender found!</option>";
						}
						else{
							foreach($calList['items'] as $item){
								echo '<option value="'.$item['id'].'">'.$item['summary'].'</option>';
							}
						}
					?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td>Event Title</td>
			<td colspan="2"><input size="60" name="gc-event-title" type="text" value="" /> </td>
		</tr>
		
		<tr>
			<td>Event Description</td>
			<td colspan="2"><textarea rows="3" cols="60"  name="gc-event-description"></textarea> </td>
		</tr>	
		
		<tr>
			<td>Event Start Date/Time</td>
			<td><input class="gc_date_picker" size="20" name="gc-event-date_start" type="text" value="" /> </td>
			<td><input class="gc_time_picker" size="20" name="gc-event-time_start" type="text" value="" /> </td>
		</tr>
		
		<tr>
			<td>Event End Date/Time</td>
			<td><input class="gc_date_picker" size="20" name="gc-event-date_end" type="text" value="" /> </td>
			<td><input class="gc_time_picker" size="20" name="gc-event-time_end" type="text" value="" /> </td>
		</tr>
		
	</table>
		
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.gc_time_picker').timepicker({
                   showNowButton: true,
                    showDeselectButton: true,
                    defaultTime: '',  // removes the highlighted time for when the input is empty.
                    showCloseButton: true
               })
		
		$('.gc_date_picker').datepicker();
		
		if($('#enable_calender_event').attr('checked') == 'checked'){
			$('#Gcalender_form').show();
		}		
		
		$('#enable_calender_event').bind('click', function(){
			if($(this).attr('checked') == 'checked'){
				$('#Gcalender_form').show();
			}
			else{
				$('#Gcalender_form').hide();
			}
						
		});
		
	});
</script>