<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gestione Catalogo Prodotti
 */
class Catalogo extends CI_Controller {

  private $map;



  /**
   * Anagrafica varianti
   * @var array string
   */
  public function __construct() {
    parent::__construct();
//    $this->load->library('pagination');
    $this->config->load('cfg_CatalogoFotoPaginazione', TRUE);
    $this->load->library('parser');


  }

  public function index() {
    $this->gestioneMedia('');

  }

  
  /**
   * Estrazione del dettaglio prodotto
   *
   * @return Array()
   *    - "path" => path completo immagine
   *    - "Siblings" => array() contenente tutte le immagini sorelle
   *
   * @param type $pathToShow  Path completa dell'immagine scelta
   */
  public function dettaglio($subfolder = "") {
    // if (!is_guest_session()) { //Negazione (default con !) view dettaglio
      $retval = $this->catalogofoto->type_content($subfolder);
      switch ($retval["code"]) {
        case "dir":
          //è stato richiamato il dettaglio senza specificafre il file quind forzo redirect
          redirect(base_url($this->catalogofoto->PathCatalog_view . $retval["source_media"]));
          exit;
          break;
        case "file":
          //ok;
          break;

        default:
          show_404(); //Risorsa non presente
          break;
      }

      $file_path = $this->catalogofoto->PathCatalog_view . $retval["source_media"];

      //estraggo il prodotto dalla sessione se esiste per impostare i valori già in carrello
      $prodotto = $this->carrellodellaspesa->get_prodotto_by_id($file_path);
      $ArrPrice = array("rows" => array());
      foreach ($this->catalogofoto->varianti as $key => $value) {

        $ArrPrice["rows"][] = array("LABELFOR" => $key, "TITLE" => $value["TITLE"]
        , "PERC_IVA" => $value["PERC_IVA"]
        , "AMOUNT_SINGOLO_PRODOTTO" => money_format('%.2n', $value["PRICE"])
        , "INPUT_ID" => $key, "INPUT_VALUE" => 0);

        if (isset($prodotto->$key)) {
          $ArrPrice["rows"][count($ArrPrice["rows"]) - 1]["selected" . $prodotto->$key->qty()] = "selected";
        }
      }

      $currentPathListFolder = explode("/", $file_path);
      array_shift($currentPathListFolder);
      array_pop($currentPathListFolder);
      $currentPathListFolder = str_replace("//", "/", implode("/", $currentPathListFolder) . "/");

//      $maplf = $this->get_dirs_and_files_whit_pagination($currentPathListFolder, array("per_page" => 8));
      /** elimkino il numero di articoli per pagina cosi da poter far sincronizzare la paginazione quando clicco sul dettagalio prodotto*/
      $maplf = $this->get_dirs_and_files_whit_pagination($currentPathListFolder);
      $maplf ["querystring"]="?per_page=".(($this->pagination->cur_page-1)*$this->pagination->per_page);

      $Thumbs = $this->load->view('section/' . __CLASS__ . '/vw_listfolder_thumb', $maplf, TRUE);

      $map = array("imgbig" => urldecode(base_url() . $file_path)
      , "template_row_price" => $this->parser->parse('section/' . __CLASS__ . '/template/vw_tabella_varianti_prezzi', $ArrPrice, true)
      // , "template_row_price" => $this->load->view('section/' . __CLASS__ . '/template/vw_tabella_varianti_prezzi_noparse', $ArrPrice, true)
      , "FORM_action" => base_url() . __CLASS__ . "/aggiungi_al_carrello"
      , "file_name" => basename(current_url())
      , "file_path" => $file_path
      , "redirecturi" => base_url($this->uri->uri_string())
      , "thumbs_template" => $Thumbs

      );
      $this->load->view('vw_header', $this->map);
      $this->load->view('section/' . __CLASS__ . '/vw_dettaglio', $map);
      $this->load->view('vw_footer');
    // } else {
    //   show_not_autorized();
    // }
  }

  
