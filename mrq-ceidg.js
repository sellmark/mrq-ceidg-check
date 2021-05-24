jQuery("#mrq_ceidg_check").click(function() {

    jQuery("#results2 .nazwafirmy").text("Trwa weryfikacja danych po stronie CEIDG...")
    jQuery("#results2 .label").addClass("hidden");
    jQuery("#results2 .adres").text('');
    jQuery("#results2 .nip").text('');
    jQuery("#results2 .regon").text('');
    jQuery("#results2 .datarozpoczecia").text('');
    jQuery("#results2 .status").text('');

   var url = "/wordpress/wp-admin/admin-ajax.php?action=mrq_ceidg"; 
   jQuery.ajax({
          type: "POST",
          url: url,
          data: jQuery("#mrq_ceidg_form").serialize(), 
          success: function(response)
          {    
               if (response.success == true) {
                  
                         let res = response.data;
                    
                         jQuery("#results2 .nazwafirmy").text(res.nazwa);
                         jQuery("#results2 .adres").text(  (res.adresDzialanosci.kod?res.adresDzialanosci.kod:'') + ' ' +(res.adresDzialanosci.miasto?res.adresDzialanosci.miasto:'') + ', ' + (res.adresDzialanosci.ulica?res.adresDzialanosci.ulica:'nr') + ' ' + res.adresDzialanosci.budynek + (res.adresDzialanosci.lokal?"/"+res.adresDzialanosci.lokal:'') + '; woj. ' + res.adresDzialanosci.wojewodztwo);
                         jQuery("#results2 .nip").text(res.wlasciciel.nip);
                         jQuery("#results2 .regon").text(res.wlasciciel.regon);
                         jQuery("#results2 .datarozpoczecia").text(res.dataRozpoczecia);
                         jQuery("#results2 .status").text(res.status);

                         jQuery("#results2 .hidden").removeClass("hidden");
                    
                    // }
                    // else{
                    //      jQuery("#results2 .nazwafirmy").text("Błędny nr NIP");
                    // }
               }
               else if (response.success == false){
                    jQuery("#results2 .nazwafirmy").text(response.data.msg);
               }
          },
          error: function(err){
               jQuery("#results2").text(err.msg);
          }
        });

   return false; 
});