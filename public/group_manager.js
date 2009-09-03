/**
 * Remove a user from a group.
 * 
 * @param string groupId
 * @param string userId
 * @param jQuery element The element to hide when removing.
 * @return boolean TRUE if removal will continue, FALSE if canceled
 * @access public
 * @since 8/28/09
 */
function removeUser (groupId, userId, element) {
	if (!confirm("Are you sure that you wish to remove this user from the group?"))
		return false;
	
	$.ajax({
		type: "POST",
		url: "index.php",
		data: {action: 'remove_member', group_id: groupId, user_id: userId},
		error: function (request, textStatus, errorThrown) {
				element.css('display', 'block');
				alert('An error has occurred, could not remove user.');
			}
		});
	
	element.hide("slow");
	return true;
}

/**
 * Add a user to a group.
 * 
 * @param string groupId
 * @param string userId
 * @param jQuery list A list to add the new user to.
 * @return boolean TRUE if removal will continue, FALSE if canceled
 * @access public
 * @since 8/28/09
 */
function addUser (groupId, userId, userName, list) {
	$.ajax({
		type: "POST",
		url: "index.php",
		data: {action: 'add_member', group_id: groupId, user_id: userId},
		error: function (request, textStatus, errorThrown) {
				alert('An error has occurred, could not add the user.');
			},
		success: function () {
				var li = list.append("<li>" + userName + " <input type='hidden' class='group_id' value='" + groupId + "'/> <input type='hidden' class='member_id' value='" + userId + "'/> <button class='remove_button'>Remove</button> </li>");

				setRemoveActions();
			}
		});
	
	return true;
}

/*********************************************************
 * Add our button actions via jQuery
 *********************************************************/
function setRemoveActions() {
	$(".group .members button.remove_button").click(function() {
		removeUser(
			$(this).siblings("input.group_id:first").attr('value'),
			$(this).siblings("input.member_id:first").attr('value'),
			$(this).parent()
		);
	});
}

$(document).ready(function() {
	
	// OnClick actions for the remove buttons
	setRemoveActions();
	
	// OnClick actions for the add buttons
	$(".group .members button.add_button").click(function() {
		addUser(
			$(this).siblings("input.group_id:first").attr('value'),
			$(this).siblings("input.new_member:first").attr('value'),
			$(this).siblings("input.new_member:first")
		);
	});
	$(".group .members input.new_member").autocomplete("index.php", {
			width: 350,
			selectFirst: false,
			extraParams: {action: 'search'}
	});
	$(".group .members input.new_member").result(function(event, data, formatted) {
		if (data) {
			addUser(
				$(this).siblings("input.group_id:first").attr('value'),
				data[1],
				data[0],
				$(this).parent().children("ul").eq(0)
			);
			$(this).attr('value', '');
		}
	});
	
});