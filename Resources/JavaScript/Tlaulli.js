$(document).ready(function(){
  /* Highlight current menu item */
  HighlightActiveMenuItem();
  
  // HTML to use for the pop-up dialog
  $('body').append('<div id="dialog">');
  $('#dialog').dialog({
    autoOpen: false,
    width: 850,
    height: 650,
    minHeight: 650,
    minWidth: 850,
    maxHeight: 650,
    maxWidth: 850,
    resizable: false,
    buttons: {
      // Add to cart from dialog.
      "Add to Cart": function() {
        if(validateSubmission($(this).parent().find('.qty').val(), $(this).parent().find('.size').val())){
          manageCart("add", $(this).parent().find('.size').val(), $(this).parent().find('.qty').val());
          $(this).dialog('close');
        }
      }, 
      "Cancel": function() { 
        $(this).dialog('close'); 
      } 
    },
    closeText: 'close'
  });
  
  $('body').append('<div id="user_msg">');
  $('#user_msg').dialog({
    title: 'Attention:',
    autoOpen: false,
    width: 450,
    buttons: {
      'OK' : function(){
        $(this).dialog('close');
      }
    }
  })
  
  // Add to cart from page.
  $('.add_to_cart').click(function(){
    if(validateSubmission($(this).parent().find('.qty').val(), $(this).parent().find('.size').val())){
      manageCart("add", $(this).parent().find('.size').val(), $(this).parent().find('.qty').val());
      $(this).parent().find('.qty').val('');
    }
  });
  
  // Display pop-up dialog.
  $('.single_item_container img.has_details').click(function(e){
    var img = $(this);
    
    // Retrieve item info and images.
    $.ajax("Resources/Libs/Ajax.php", 
    {
     data: {getItem: {'id' : img.parent().attr('id'), 'with_imgs' : true,
         'with_ws_pricing' : true, 'with_sizes' : true}},
     dataType: 'json',
     beforeSend: function(){
       $('#dialog').dialog('option', 'title', img.attr('alt').replace('Apron', ''));
       $('#dialog').html("<img src='Resources/Imgs/LoadingSymbol2.gif' alt='Loading...'/>");
       $('#dialog').dialog('open');
     },
     success: function(data){
       if(data.error){
         alert(data.error);
         return;
       }
       
       var description = data.prim_color + (data.sec_color ? ' w/ ' + data.sec_color : '') + ' ' +
           data.style + ' ' + data.item_type;
        
       var output = '<!-- Item, Size, and Qty -->' +
         '<div class="double_item_container" id="' + data.item_id + '">' +
            '<div class="image_container">' +
                '<a href="' + (data.imgs[0] ? data.imgs[0].loc.replace(/Nm\.JPG/, 'Zm.JPG') : '#') + '" class="jqzoom" title="' + description + '">' +
                    '<img src="' + data.imgs[0].loc + '" alt="' + description + '" />' +
                '</a>'+
                '<a href="' + (data.imgs[1] ? data.imgs[1].loc.replace(/Nm\.JPG/, 'Zm.JPG') : '#') + '" class="jqzoom" title="' + description + '">' +
                    '<img src="' + data.imgs[1].loc + '" alt="' + description + '" />' +
                '</a>'+
                '<br style="clear: both;"/>' +
            '</div>' +
            '<div>';
        
        // Currently there is only a one-size-fits-all for rounded waist.
        output += "<label class='label'>Size:</label>";
        if(data.style != 'rounded_waist'){
          output += "<select class='size'>";
        
          for(var s in data.sizes){
            if(data.sizes[s].size_letter != "SP"){
              output += '<option value="' + data.sizes[s].item_id + '">' +
                getSizeWord(data.sizes[s].size_letter) + '</option>';
            }
          }
        } else {
          output += "<select class='size' disabled='disabled'>" +
                        "<option value='" + data.item_id + "'>" +
                          "One Size Fits All" +
                        "</option>";
        }
        output += "</select>" +
          '</div>' +
            '<div>' +
                '<label class="label">Quantity: </label>'  +
                '<input type="text" class="qty"/>' +
            '</div>' +
         '</div>' +
         
         '<!-- Description and whole sale pricing -->' +
         '<div class="description_container">' +
            '<div>' +
                '<h3>Features</h3>' +
                '<ul class="feature_list">';
    
       var des = data.des.split('\n');
       for(var d in des){
         output += '<li>' + des[d] + '</li>';
       }
     
       output += '</ul>' +
            '</div>' +
            '<table class="pricing">' +
                '<thead>' +
                    '<tr>' +
                        '<th>Quantity</th>' +
                        '<th>Price</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>';
    
       for(var p in data.ws_pricing){
         output += '<tr>' +
                     '<td>' + data.ws_pricing[p].l_qty + 
            (data.ws_pricing[p].h_qty != null ? ' to ' + data.ws_pricing[p].h_qty : '+') +
                     '</td>' +
                     '<td>$' + data.ws_pricing[p].price + ' each</td>' +
                   '</tr>';
       }
    
       output += '</tbody>' +
             '</table>' +
         '</div>';
       
       $('#dialog').html(output);
       $('.jqzoom').jqzoom({
           zoomWidth: 450,
           zoomHeight: 450
       });
     },
     error: function(data){
        $.each(data, function(key, val){
          alert('key=' + key + ', val=' + val);
        });
        //alert('State.php: Failed to call getStates.');
     }
    });
  });
  
  $("#cont_shop").click(function(){
    changePage("CobblerApr", "Aprons");
  });
  
  // Delete the item from the session and the UI.
  $(".chko_delete").click(function(){
    var row = $(this).parents("tr");
    manageCart("delete", row.attr("id"));
    row.remove();
    
    if($("table#summary tr[id]").length == 0){
        $("table#summary_calc").find(".calc_heading").parents("tr").remove();
        $("table#summary tr:eq(0)").after("<tr><td colspan='4'>" +
            "No items in the cart. Please continue shopping.</td></tr>");
        $("table#summary_calc").find("input[type='submit']").css("display", "none");
    } else {
        $("table#summary tr[id]").each(function(idx){
          $(this).find("input[name^='item_name']").attr("name", "item_name_" + (idx + 1));
          $(this).find("input[name^='amount']").attr("name", "amount_" + (idx + 1));
          $(this).find("input[name^='quantity']").attr("name", "quantity_" + (idx + 1));
          $(this).find("input[name^='item_number']").attr("name", "item_number_" + (idx + 1));
        });
        
        updateCalculations();
    }
  });
  
  // Update the quantity
  $(".chko_qty").blur(function(){
    var row = $(this).parents("tr");
    
    if(!isNaN(parseInt($(this).val())) && parseInt($(this).val()) >= 1){
      manageCart("update", row.attr("id"), $(this).val());
      
      // Update the quantity and amount of the row.
      row.find('.summary_amount').text(row.find("input[name^='amount']").val() * $(this).val());
      row.find("input[name^='quantity']").val($(this).val()); 
      
      updateCalculations();
     
      // Enable payment button only if all rows have valid values.
      var all_valid = true;
      $('.chko_qty').each(function(){
        if(isNaN(parseInt($(this).val())) || parseInt($(this).val()) < 1){
          all_valid = false;
        }
      });
      
      if(all_valid){
        $("table#summary_calc").find("input[type='submit']").attr("disabled", false);
      }
    } else {
      $('#user_msg').html('Please enter a quantity greater than or equal to 1.');
      $('#user_msg').dialog('open');
      $("table#summary_calc").find("input[type='submit']").attr("disabled", true);
    }
  });
  
  $('.sticky').cluetip({cluetipClass: 'jtip', arrows: true, dropShadow: false, hoverIntent: false, showTitle: false,
    leftOffset: -165});
  
  $('.single_item_container').hover(
    function () {
        $('.show_details, .view_more', this).show();
    },
    function () {
        $('.show_details, .view_more', this).hide();
    }
  );
      
  $('.single_item_container .show_details').click(function(){
      $(this).parent().find('img.has_details').click();
  });
  
  $('.single_item_container .view_more').click(function(){
      $(this).parent().find('a').click();
  });
  
  $("#yes_free_apron").click(function(){
    changePage("RoundedWaistApr", "Rounded Waist Aprons");
  });
});

/*
 * Links to another page with the home page as the master page.
 */
function changePage(page, title){
  window.location.href='index.php?page=' + page + "&title=" + title;
  void(0);
}

/*
 * Opens a new window.
 */
function OpenWin(Img) {
  var Win = window.open('', '', 'width=520, height=400, resizable=no, menubar=no');
  Win.document.write("<img src='Resources/Imgs/" + Img + "' alt='Food Image'/>");
  Win.focus();
}

/* 
 * Highlights the menu item link of the current page.
 */
function HighlightActiveMenuItem(){
  if($('#home').length > 0){
    $('.home').addClass('highlight_menu_item');
  } else if($('#aprons').length > 0){
    $('.aprons').addClass('highlight_menu_item');
  } else if($('#aprons_cobbler').length > 0){
    $('.aprons_cobbler').addClass('highlight_menu_item');
  } else if($('#aprons_bib').length > 0){
    $('.aprons_bib').addClass('highlight_menu_item');
  } else if($('#aprons_waist_down').length > 0){
    $('.aprons_waist_down').addClass('highlight_menu_item');
  } else if($('#aprons_rounded_waist').length > 0){
    $('.aprons_rounded_waist').addClass('highlight_menu_item');
  } else if($('#aprons_squared_waist').length > 0){
    $('.aprons_squared_waist').addClass('highlight_menu_item');
  } else if($('#aprons_butcher').length > 0){
    $('.aprons_butcher').addClass('highlight_menu_item');
  } else if($('#uniforms_nurse').length > 0){
    $('.uniforms_nurse').addClass('highlight_menu_item');
  } else if($('#uniforms_security').length > 0){
    $('.uniforms_security').addClass('highlight_menu_item');
  } else if($('#salon_capes').length > 0){
    $('.salon_capes').addClass('highlight_menu_item');
  } else if($('#accessories_ties').length > 0){
    $('.accessories_ties').addClass('highlight_menu_item');
  } else if($('#accessories_earrings').length > 0){
    $('.accessories_earrings').addClass('highlight_menu_item');
  } else if($('#table_cloths').length > 0){
    $('.table_cloths').addClass('highlight_menu_item');
  } else if($('#table_napkins').length > 0){
    $('.table_napkins').addClass('highlight_menu_item');
  } else if($('#bags_laundry_bags').length > 0){
    $('.bags_laundry_bags').addClass('highlight_menu_item');
  } 
}

// Updates cart.
function manageCart(action, id, qty){
  var url = 'Resources/Components/CartMgr.php?' + action + '&id=' + id + (action == 'delete' ? '' : '&qty=' + qty);
  $.get(url, function(data){
    // Display error message.
    if(typeof data.error != 'undefined'){
      $('#user_msg').html(data.error);
      $('#user_msg').dialog('open');
    }
    
    // Update cart on screen.
    $('#num_items').text(data.num_items);
    var total = new BigDecimal(data.total.toString()).format(-1, 2);
    $('#total').text("$ " + total);
  }).error(function(e){
    alert('Unable to update cart: e=' + JSON.stringify(e));
  });
}

// Validates an order submission.
function validateSubmission(qty, size){
  if(size == ''){
    $('#user_msg').html('Please specify the size desired.');
    $('#user_msg').dialog('open');
    return false;
  } else if(isNaN(parseInt(qty)) || parseInt(qty) < 1){
    $('#user_msg').html('Please enter a quantity greater than or equal to 1.');
    $('#user_msg').dialog('open');
    return false;
  }
  
  return true;
}

// Retrieves the total shipping cost.
function getShippingCost(count){
  var shipping_cost = 0;
  $.ajax('Resources/Libs/Ajax.php',
    {
      data: {getShippingCost: {'count' : count}},
      dataType: 'json',
      async: false,
      success: function(data){
          if(typeof data.error != 'undefined'){
            $('#user_msg').html(data.error);
            $('#user_msg').dialog('open');
          }
          
          shipping_cost = data.shipping_cost;
      }
    }).error(function(e){
      alert('Unable to determine shipping cost: e=' + JSON.stringify(e));
    });
    
    return shipping_cost;
}

// Retrieves the discount applicable to the entire cart.
function getDiscountedCost(items, total_items){
  var discount;
  $.ajax('Resources/Libs/Ajax.php',
    {
      data: {getDiscountedCost: {'items' : items, 'total_items': total_items}},
      dataType: 'json',
      async: false,
      success: function(data){
          if(typeof data.error != 'undefined'){
            $('#user_msg').html(data.error);
            $('#user_msg').dialog('open');
          }
          
          discount = data;
      }
    }).error(function(e){
      alert('Unable to determine discount: e=' + JSON.stringify(e));
    });
    
    return discount;
}

function updateCalculations(){
    // Determine the total amount of all rows.
    var total_amount = new BigDecimal('0');
    $("table#summary").find(".summary_amount").each(function(){
        total_amount = total_amount.add(new BigDecimal($(this).text()));
    });

    // Update the cost calculations.
    var items = {};
    var total_items = 0;
    $("table#summary tr[id]").each(function(){
        items[parseInt($(this).attr("id"))] = $(this).find('.chko_qty').val(); 
        total_items += parseInt($(this).find('.chko_qty').val());
    });
    
    var result = getDiscountedCost(items, total_items);
    var discounted_cost = new BigDecimal(result.discounted_cost.toString());
    var discount = total_amount.subtract(discounted_cost);
    var tax_rate = new BigDecimal($("table#summary_calc").find("input[name='tax_rate']").val().toString());
    var tax =  tax_rate.multiply(discounted_cost);
    $("table#summary_calc").find('#summary_total_amt').text(total_amount.format(-1, 2));
    $("table#summary_calc").find("#summary_discount").text(discount.format(-1, 2));
    $("table#summary_calc").find("#summary_subtotal").text(discounted_cost.format(-1, 2));
    $("table#summary_calc").find("#summary_tax").text(tax.format(-1, 2));

    // Update shipping cost.
    var count = 0;
    $("table#summary").find("input[name^='quantity']").each(function(){
        count += parseInt($(this).val());
    });

    var shipping_cost = new BigDecimal(getShippingCost(count).toString());
    var total = discounted_cost.add(tax.add(shipping_cost));
    $("table#summary_calc").find("input[name='shipping_1']").val(shipping_cost.format(-1, 2));
    $("table#summary_calc").find("input[name='discount_amount_cart']").val(discount.format(-1, 2));
    $("table#summary_calc").find("input[name='tax_cart']").val(tax.format(-1, 2));
    $("table#summary_calc").find("#summary_sh").text(shipping_cost.format(-1, 2));
    $("table#summary_calc").find("#summary_total").text("$ " + total.format(-1, 2));
    
    // Update informational messages.
    if(result.free_items > 0) {
      $("table#message #congrats").html("Congratulations. You are getting " + result.free_items +
        " free apron" + (result.free_items > 1 ? "s!" : "!"));
    } else {
      $("table#message #congrats").html("");
    }
    
    if(result.entitled_free_items > result.free_items) {
      var additional = result.entitled_free_items - result.free_items;
      $("table#message #upsell").html("<br/>Congratulations. You qualify for " +
        (result.free_items > 0 ? "an additional " : "") + additional + 
        " free rounded waist apron" + (additional > 1 ? "s" : "") + ". Would you like to get " +
        (additional > 1 ? "them" : "it") + " now? <input type='button' value='Yes' id='yes_free_apron' class='button'/>");
    } else {
      $("table#message #upsell").html("");
    }
}

function getImgPerspective(img, perspective){
   return img.replace(/[a-zA-Z]{2}\.JPG/, perspective[0].toUpperCase() + perspective[1] + '.JPG');
}

function getSizeWord(size){
  switch(size){
    case "SP":
      return "X-Small";
    case "S":
      return "Small";
    case "M":
      return "Medium";
    case "L":
      return "Large";
    case "XL":
      return "X-Large";
    default:
      return null;
  }
}