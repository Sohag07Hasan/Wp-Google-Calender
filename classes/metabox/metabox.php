<div class="wrap">
	<h2>Insert an Event to the Google calender </h2>
	<p> Enable G calender event <input id="enable_calender_event" type="checkbox" name="enable_calender_event" value="1" /></p>
	<table  class="form-table" id="Gcalender_form" style="display: none;">
		<tr>
			<td>Select a calender</td>
			<td colspan="2">
				<select>
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
			<td>Event Date</td>
			<td colspan="2"><input id="gc_date_picker" size="60" name="gc-event-date" type="text" value="" /> </td>
		</tr>
		<tr>
			<td>Event Time</td>
			<td colspan="2"><input id="gc_time_picker" size="60" name="gc-event-time" type="text" value="" /> </td>
		</tr>
		
	</table>
		
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#gc_time_picker').timepicker({
                   showNowButton: true,
                    showDeselectButton: true,
                    defaultTime: '',  // removes the highlighted time for when the input is empty.
                    showCloseButton: true
               })
		
		$('#gc_date_picker').datepicker();
		
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