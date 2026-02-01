</div> <!-- container -->
	

	<!-- file input -->
	<script src="assests/plugins/fileinput/js/plugins/canvas-to-blob.min.js" type="text/javascript"></script>	
	<script src="assests/plugins/fileinput/js/plugins/sortable.min.js" type="text/javascript"></script>	
	<script src="assests/plugins/fileinput/js/plugins/purify.min.js" type="text/javascript"></script>
	<script src="assests/plugins/fileinput/js/fileinput.min.js"></script>	
<script src="custom/js/panel.js"  type="text/javascript"></script>


	<!-- DataTables -->
	<script src="assests/plugins/datatables/jquery.dataTables.min.js"></script>

	<script type="text/javascript">

$(document).ready(function() {
       // $("input:text:visible:first").focus();

// Map [Enter] key to work like the [Tab] key
// Daniel P. Clark 2014
 
// Catch the keydown for the entire document
$(document).keydown(function(e) {
 
  // Set self as the current item in focus
  var self = $(':focus'),
      // Set the form by the current item in focus
      form = self.parents('form:eq(0)'),
      focusable;
 
  // Array of Indexable/Tab-able items
  focusable = form.find('input,a,select,button,textarea,div[contenteditable=true]').filter(':visible');
 
  function enterKey(){
    if (e.which === 13 && !self.is('textarea,div[contenteditable=true]')) { // [Enter] key
 
      // If not a regular hyperlink/button/textarea
      if ($.inArray(self, focusable) && (!self.is('a,button'))){
        // Then prevent the default [Enter] key behaviour from submitting the form
        e.preventDefault();
      } // Otherwise follow the link/button as by design, or put new line in textarea
 
      // Focus on the next item (either previous or next depending on shift)
      focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).focus();
       focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).select();
 

      return false;
    }
  }
  // We need to capture the [Shift] key and check the [Enter] key either way.
  if (e.shiftKey) { enterKey() } else { enterKey() }
});

});

</script>
<script>
$('select[name="clauses"]').children('option:contains(OR)').val();

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.ctrlKey && evt.keyCode == 191) {
        addRow();
    }
};


function myFunction(x) {
    $x(".group_tag_dynamic").hide('slow');
}
 

</script>



</body>
</html>

<script>
        document.onkeyup = function(e) {
  if (e.altKey && e.which == 82) {
    //p press
   $("#paid").focus();
   // subAmount();
  } 

 
  if (e.altKey && e.which == 83) {
    //r press
   $("#createOrderBtn").submit();

  } 
   if (e.altKey && e.which == 80) {
    //p press
    $("#printorder").click();
  

  }  if (e.altKey && e.which == 78) {
    //n press
    $("#neworder").click();
  

  } 

  if (e.altKey && e.which == 67) {
    //n press
    $("#clientName").focus();
  

  } 



}; 
</script>