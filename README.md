# Tablet API (ALT - Anoto Live tablet)

If working on this project, please bear in mind that it needs to work
with **BOTH** Formidable 2.13 _AND_ Live forms releases. This will remain
the case until all customers can be suitably migrated to the ALF platform.

Things that the API needs to do

1. Login/Authentication
  * Basic HTTP authentication, already provided and not managed by API

2. List of form templates/models - completed
  * Name, Description, ID, Created Date, Modified Date

3. Download of form template with background image - completed
  * Config XML and background PNG's (base 64 encoded files - PNG's are cached for speed)

4. List of submissions by form
  * Ideally we can grab a paged list of 20-50 submissions at a time. 
  * Each item in list would contain: formIdentifier, Last Editor, Last Edit Date, Form State (Valid/Invalid, mandatory fields etc), form reference/key fields (if available)

5. Get form data by formIdentifier
  * For integration into the form layout. 
  * Retrieval of the strokes on a page by page basis, either by themselves or rendered onto the background would allow pen data display within the tablet editor.

6. Update form data by formIdentifier.
  * To update text fields, radio/check boxes, ideally by sending a single xml document to the server. 
  * An additional call to update drawing/signature fields would be a good feature.

7. Begin a new form.
  * To create and assign pattern to a form with new data. 
  * This would need to be a synchronous call to return the new formIdentifier. 
  * For the ALE implementation we were able to use the pre-population API of the platform.

8. Get last modified date by formUnique (or by array of formUniques)
  * To allow tablet app to poll for changes to cached forms.
    - Better as a sub-function of point 5
