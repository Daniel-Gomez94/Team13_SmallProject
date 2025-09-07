# **Team13_SmallProject** 
Small project repo for team 13

---

## Database (Daniel Gomez)
- Designed the schema (`schema_clean.sql`) for Users and Contacts tables.
- Enforced foreign key with ON DELETE CASCADE so when a user is deleted, their contacts are removed automatically.
- Added unique constraints (e.g., each user cannot have duplicate emails).
- Created indexes for fast searching by name, email, and phone.
- Built the ERD (`ERD.pdf`) to visualize relationships between Users and Contacts.
- Verified schema with sample data inserts for testing.

---

## Project Management (Daniel Gomez)
- Set up Trello board for task tracking and team coordination.
- Created Gantt chart to show weekly milestones and deadlines.
- Organized repo structure into `database/`, `api/`, `frontend/`, `docs/`, and `management/`.
- Ensured deliverables (ERD, schema, presentation skeleton) were ready early for teammates.

---

## COP 4331 Contacts App API
This repository contains the API specification and backend code for the Contacts App project.

### Structure 
- `contacts-api.yaml` → Swagger/OpenAPI spec (defines the API contract)
- `LAMPAPI/` → PHP backend files will go here (`Login.php`, `AddContact.php`, `SearchContacts.php`, etc.) the color named ones just need to be altered

### Next Steps
1. **Backend:**  
   - Implement the PHP endpoints (`Login.php`, `AddContact.php`, `SearchContacts.php`) inside the `LAMPAPI/` folder. The color files just need to be changed to match API
   - Make sure each endpoint matches the request/response format in `contacts-api.yaml`.
2. **Database setup:**  
   - Create MySQL tables for users and contacts.  
   - Add a simple seed record for testing login and search.
3. **Testing with Postman:**  
   - Import `contacts-api.yaml` or create requests manually.  
   - Use `POST http://localhost/LAMPAPI/Login.php` (or server IP) with JSON bodies from the spec.  
   - Verify responses match the definitions.
   - I have a fake URL/Domain in the API for right now, I will be ready to make changes as soon as I can get more info
---
### Notes
- Once the server is deployed, update the `host` and `basePath` fields in the spec with the real server info.
