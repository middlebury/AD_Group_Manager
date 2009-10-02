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

				setMemberActions();
			}
		});
	
	return true;
}

/**
 * Create a group
 *
 * @param string groupName
 * @param string containerDN
 * @param jQuery containerElement A container to add the new group to.
 * @return boolean TRUE if removal will continue, FALSE if canceled
 * @access public
 * @since 8/28/09
 */
function createGroup (groupName, containerDN,  containerElement) {
	$.ajax({
		type: "POST",
		url: "index.php",
		data: {action: 'create_group', container_dn: containerDN, new_group_name: groupName},
		error: function (request, textStatus, errorThrown) {
				alert('An error has occurred, could not create the group.');
			},
		success: function (data, textStatus) {
				containerElement.append(data);

				setGroupActions();
				setMemberActions();
			}
		});

	return true;
}

/**
 * Delete a group.
 *
 * @param string groupId
 * @param jQuery element The element to hide when removing.
 * @return boolean TRUE if removal will continue, FALSE if canceled
 * @access public
 * @since 8/28/09
 */
function deleteGroup (groupId, element) {
	if (!confirm("Are you sure that you wish to permenantly delete this group?"))
		return false;

	$.ajax({
		type: "POST",
		url: "index.php",
		data: {action: 'delete_group', group_id: groupId},
		error: function (request, textStatus, errorThrown) {
				alert('An error has occurred, could not remove user.');
				element.show("slow");
			}
		});

	element.hide("slow");
	return true;
}

/*********************************************************
 * Add our button actions via jQuery
 *********************************************************/

function setMemberActions() {
	// Onclick actions for the remove buttons
	$(".group .members button.remove_button").click(function() {
		removeUser(
			$(this).siblings("input.group_id:first").attr('value'),
			$(this).siblings("input.member_id:first").attr('value'),
			$(this).parent()
		);
	});
}

function setGroupActions() {
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
				$(this).parent().parent().children("ul").eq(0)
			);
			$(this).attr('value', '');
		}
	});
	
	// Set the delete-group actions
	$(".group button.delete_button").click(function() {
		deleteGroup(
			$(this).siblings("input.group_id:first").attr('value'),
			$(this).parents('.group')
		);
	});
	
	// Set the manager change actions
	$(".group button.change_manager").click(function() {
		if ($(this).text() == 'Change') {
			$(this).text("Cancel");
			$(this).siblings("form.change_manager_form").show('slow');
		} else {
			$(this).text("Change");
			$(this).siblings("form.change_manager_form").hide('slow');
		}
	});
	$(".group form.change_manager_form").submit(function() {
		var groupElement = $(this).parents("fieldset.group:first");
		
		var newManager = $(this).children("input[name=new_manager]:first").attr('value');
		if (!newManager.length) {
			return false;
		}
		
		if (confirm("Are you sure you wish to change the manager?\n\nYou will no longer be able to manage this group yourself.")) {
			$.ajax({
				type: "POST",
				url: "index.php",
				data: {
					action: 'change_manager', 
					group_id: $(this).children("input[name=group_id]:first").attr('value'), 
					new_manager: newManager
				},
				error: function (request, textStatus, errorThrown) {
						alert('An error has occurred, could not change the manager of the group.');
					},
				success: function (data, textStatus) {
						groupElement.replaceWith(data);
						
						setGroupActions();
						setMemberActions();
					}
				});
		}
		return false;
	});
	$(".group form.change_manager_form input.new_manager_search").autocomplete("index.php", {
		width: 350,
		selectFirst: false,
		extraParams: {action: 'search'}
	});
	$(".group form.change_manager_form input.new_manager_search").result(function(event, data, formatted) {
//		console.log(data);
		if (data) {
			$(this).siblings("input[name=new_manager]:first").attr('value', data[1]);
		}
	});
}

$(document).ready(function() {

	// OnClick actions for the add and remove buttons
	setMemberActions();

	// Set the delete-group actions
	setGroupActions();

	$("#create_group_form").submit(function() {
		var name = $(this).find('#new_group_name');
		if (name.attr('value').length) {
			createGroup(
				name.attr('value'),
				$(this).find("#new_group_container_dn").attr('value'),
				$('#groups').eq(0)
			);
			name.attr('value', '');
		} else {
			alert("You must enter a group name.");
		}

		return false;
	});
	
});
