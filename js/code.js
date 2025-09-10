const urlBase = 'http://138.197.87.182/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function doLogin()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
	let hash = md5(password);
	
	document.getElementById("loginResult").innerHTML = "";

	let tmp = {login:login, password:hash};
	let jsonPayload = JSON.stringify(tmp);
	
	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4) 
			{
				if (this.status == 200) {
					let jsonObject = JSON.parse( xhr.responseText );
					userId = jsonObject.id;
			
					if( userId < 1 )
					{		
						document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
						return;
					}
			
					firstName = jsonObject.firstName;
					lastName = jsonObject.lastName;

					saveCookie();
		
					window.location.href = "contacts.html";
				} else if (this.status == 401) {
					document.getElementById("loginResult").innerHTML = "Invalid username or password";
				} else if (this.status == 403) {
					document.getElementById("loginResult").innerHTML = "Access forbidden - CORS issue";
				} else {
					document.getElementById("loginResult").innerHTML = `Error: ${this.status} - ${this.statusText}`;
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
		// Load contacts when page loads
		searchContacts();
	}
}

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function addContact()
{
	let newFirstName = document.getElementById("firstNameText").value.trim();
	let newLastName = document.getElementById("lastNameText").value.trim();
	let newEmail = document.getElementById("emailText").value.trim();
	let newPhone = document.getElementById("phoneText").value.trim();
	
	document.getElementById("contactAddResult").innerHTML = "";

	// Validation
	if (!newFirstName || !newLastName) {
		document.getElementById("contactAddResult").innerHTML = "First name and last name are required";
		return;
	}

	let tmp = {
		userId: userId,
		firstName: newFirstName,
		lastName: newLastName,
		email: newEmail,
		phone: newPhone
	};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/AddContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4) 
			{
				if (this.status == 201 || this.status == 200) {
					document.getElementById("contactAddResult").innerHTML = "Contact has been added successfully";
					// Clear the form
					document.getElementById("firstNameText").value = "";
					document.getElementById("lastNameText").value = "";
					document.getElementById("emailText").value = "";
					document.getElementById("phoneText").value = "";
					// Refresh the contact list
					searchContacts();
				} else {
					let response = JSON.parse(xhr.responseText);
					document.getElementById("contactAddResult").innerHTML = response.error || "Failed to add contact";
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("contactAddResult").innerHTML = err.message;
	}
}

function searchContacts()
{
	let srch = document.getElementById("searchText") ? document.getElementById("searchText").value : "";
	if (document.getElementById("contactSearchResult")) {
		document.getElementById("contactSearchResult").innerHTML = "";
	}
	
	let tmp = {search: srch, userId: userId};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/SearchContacts.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse(xhr.responseText);
				
				if (document.getElementById("contactSearchResult")) {
					if (jsonObject.results.length > 0) {
						document.getElementById("contactSearchResult").innerHTML = `Found ${jsonObject.results.length} contact(s)`;
					} else {
						document.getElementById("contactSearchResult").innerHTML = "No contacts found";
					}
				}
				
				displayContacts(jsonObject.results);
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		if (document.getElementById("contactSearchResult")) {
			document.getElementById("contactSearchResult").innerHTML = err.message;
		}
	}
}

function displayContacts(contacts)
{
	let contactList = "";
	
	if (contacts.length === 0) {
		contactList = "<p>No contacts to display</p>";
	} else {
		contactList = "<div class='contacts-grid'>";
		for (let i = 0; i < contacts.length; i++) {
			let contact = contacts[i];
			contactList += `
				<div class='contact-card'>
					<div class='contact-name'>${contact.firstName} ${contact.lastName}</div>
					<div class='contact-details'>
						${contact.email ? `<div><i class="fa fa-envelope"></i> ${contact.email}</div>` : ''}
						${contact.phone ? `<div><i class="fa fa-phone"></i> ${contact.phone}</div>` : ''}
					</div>
					<div class='contact-actions'>
						<button onclick='editContact(${contact.id})' class='edit-btn'><i class="fa fa-edit"></i> Edit</button>
						<button onclick='deleteContact(${contact.id})' class='delete-btn'><i class="fa fa-trash"></i> Delete</button>
					</div>
				</div>
			`;
		}
		contactList += "</div>";
	}
	
	document.getElementById("contactList").innerHTML = contactList;
}

function deleteContact(contactId)
{
	if (!confirm("Are you sure you want to delete this contact?")) {
		return;
	}

	let tmp = {id: contactId, userId: userId};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/DeleteContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4) 
			{
				if (this.status == 200) {
					// Refresh the contact list
					searchContacts();
				} else {
					alert("Failed to delete contact");
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		alert("Error deleting contact: " + err.message);
	}
}

function editContact(contactId)
{
	// For now, just show an alert. You can implement a full edit form later
	alert("Edit functionality not yet implemented for contact ID: " + contactId);
}

function doRegister()
{
	let firstName = document.getElementById("registerFirstName").value.trim();
	let lastName = document.getElementById("registerLastName").value.trim();
	let username = document.getElementById("registerUsername").value.trim();
	let password = document.getElementById("registerPassword").value;

	document.getElementById("registerResult").innerHTML = "";

	if (!firstName || !lastName || !username || !password) {
		document.getElementById("registerResult").innerHTML = "All fields are required.";
		return;
	}

	// Hash the password with md5 before sending
	let hash = md5(password);

	let tmp = {
		firstName: firstName,
		lastName: lastName,
		login: username,
		password: hash
	};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/CreateUsers.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4) 
			{
				if (this.status == 200 || this.status == 201) {
					let jsonObject = JSON.parse(xhr.responseText);
					if (jsonObject.error) {
						document.getElementById("registerResult").innerHTML = jsonObject.error;
					} else {
						document.getElementById("registerResult").innerHTML = "Registration successful! You may now log in.";
						document.getElementById("registerDiv").style.display = "none";
					}
				} else {
					document.getElementById("registerResult").innerHTML = "Registration failed.";
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("registerResult").innerHTML = err.message;
	}
}
