// currentLocat=window.location.href;
// var url = new URL(currentLocat);
// var view = url.searchParams.get("edit_quotation_id");

$(document).ready(function () {

 	 $('.data-table').DataTable();
      $('.dataTableDesc').DataTable(
      {
        autoWidth: true,
        "lengthMenu": [
          [10, 20, 50, -1],
          [10, 20, 50, "All"]
        ],
         "order": [[ 0, "desc" ]]
      });
var table;

const createdCell = function(cell) {
    let original;
//  cell.setAttribute('contenteditable', true)
  cell.setAttribute('spellcheck', false)
  cell.addEventListener("focus", function(e) {
        original = e.target.textContent
    })
  cell.addEventListener("blur", function(e) {
        if (original !== e.target.textContent) {
        const row = table.row(e.target.parentElement)
        row.invalidate()
        
        var data=row.data();
        //console.log('Row changed: ', data);

        $.ajax({
            url: 'php_action/product_action.php',
            type: 'post',
            data: {setProductEdit: data[0],product_name:data[1],rate:data[2],alert_at:data[3]},
            dataType: 'json',
            success:function(response) {
                $('.responseAlert').html('<div class="text-center alert alert-'+response.sts+'">'+
                '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.msg +
                  '</div>');
                $(".alert").fadeOut(2000);
                    
            }


        });
        }
  })
}

table = $('#example').DataTable({
  columnDefs: [{ 
    targets: '_all',
    createdCell: createdCell
  }]
}) 

    // $('.select2').select2();
    // $('.select2').css("width","100%");
   // $('.my-colorpicker2').colorpicker();

   //  $('.my-colorpicker2').on('colorpickerChange', function(event) {
   //    $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
   //  });
}); //end of jquery ready


function deleteData(table,fld,id,url){

      var x = confirm(' Do you want to ID# : '+id);

        if (x==true) {

           $.ajax({

          url:"php_action/ajax_deleteData.php",

          type:"post",

          data:{table:table,fld:fld,delete_id:id,url:url},

          dataType:"json",

          success:function(response){
             $(".response").html('<div class="alert alert-'+response.sts+' text-center">'+response.msg+'</div>');
                


            setTimeout(function(){

               window.location=url;

              $(".response").html('');

            },1500);

          }

        });

      }
}
$("#add_nav_menus_fm").on('submit',function(e) {

        e.preventDefault();
        e.stopPropagation(); 
        var form = $('#add_nav_menus_fm');
      
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            dataType:'json',
            beforeSend:function() {
                $('#add_nav_menus_btn').prop("disabled",true);
                // $('#saveData1').text("Loading...");
            },
            success:function (responeID) {
               
                $('#add_nav_menus_btn').prop("disabled",false);
                $('#add_nav_menus_fm').each(function(){
                    this.reset();
                });    
                if (responeID.sts=="success") {
                sweeetalert("Added","Menu has been Added",'success',2000);
                $("#add_nav_table").load(location.href + " #add_nav_table");
                }
                if (responeID.sts=="info") {
                sweeetalert("Update","Menu has been Updated",'info',2000);
                $("#add_nav_table").load(location.href + " #add_nav_table");
                }
 
            
            }
        });//ajax call
    });//main    
function sweeetalert(title,text,status,time) {
         swal({
                          title: title,
                          text: text,
                          type : status,
                          icon: status,
                          timer: time,
                          buttons: false,
                          showCancelButton: false,
                          showConfirmButton: false
                        }); 
}