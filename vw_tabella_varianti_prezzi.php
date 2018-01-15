<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<table class="tableprice table table-condensed table-striped table-hover table-responsive">
  <?php
  echo $ArrPrice["rows"];
 ?>
  <tbody>
    {rows}


    <tr data-container="body" data-trigger="hover" data-toggle="{INPUT_ID}" data-placement="right"  data-content="Per ricevere tutte le foto dell' atleta scelto, SELEZIONARE SOLO UNA QUANTITA'. Annota i numeri che vedi sovraimpressi sulle foto e completa l'ordine">
      <th><label for="{LABELFOR}"></label></th>

      {if INPUT_ID == 'vars_s'}
        qui
        {/if}

      <?php

      if ($a=="Stampa 30x45") {
        echo "string";
      }
       ?>
      <th><label for="{LABELFOR}">{TITLE}</label></th>
      <th class="prezzo">&euro; {AMOUNT_SINGOLO_PRODOTTO}</th>
      <td class="qty">
        <input type="hidden" name="{INPUT_ID}[iva]" value="{PERC_IVA}">
        <input type="hidden" name="{INPUT_ID}[amount_singolo_prodotto]" value="{AMOUNT_SINGOLO_PRODOTTO}">
        <select class="form-control" name="{INPUT_ID}[qty]">
          <option {selected0}>0</option>
          <option {selected1}>1</option>
          <option {selected2}>2</option>
          <option {selected3}>3</option>
          <option {selected4}>4</option>
          <option {selected5}>5</option>
          <option {selected6}>6</option>
          <option {selected7}>7</option>
          <option {selected8}>8</option>
          <option {selected9}>9</option>
          <option {selected10}>10</option>
        </select>
      <!-- <input type="checkbox" name="{vars_all}[qty]" value="1"> -->
      </td>
    </tr>
    {/rows}
  </tbody>
  <tfoot>
    <td colspan="4" class="text-center">
      <button type="submit" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> Aggiungi al carello</button>
    </td>
  </tfoot>
</table>
