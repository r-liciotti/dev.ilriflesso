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
   * Estrae la lista delle directory e dei file nella cartella $_absolute_path
   * @param type $_absolute_path
   * @return array
   */
  private function get_dirs_and_files_whit_pagination($_absolute_path, $configPagination = array()) {
    $arrRet = array();
    $absolute_path = urldecode($_absolute_path);
    $arr_content_folder = $this->catalogofoto->get_content_Folder($absolute_path, 1, FALSE, ORD_TYPE_KEY, ORD_VAL_ASCENDENTE);
    $dir_and_files = $arr_content_folder["contents"];

    $config = $this->config->item('cfg_CatalogoFotoPaginazione');
    foreach ($configPagination as $key => $val) {
      if (isset($config[$key])) {
        $config[$key] = $val;
      }
    }


    $config ['total_rows'] = count($dir_and_files["files"]);
    $this->pagination->initialize($config);
    $pagination = $this->pagination->create_links();


    $dir_and_files["files"] = array_slice($dir_and_files["files"], (($this->pagination->cur_page - 1) * $config['per_page']), $config['per_page']);

    $arrRet["currentPath"] = $arr_content_folder["urlChild"];
    $arrRet["currentPathDettaglio"] = base_url($arr_content_folder["path"]);
//    $arrRet["segmentPhisical"]=$segmentPhisical;
//    $arrRet["baseFolderCatalogo"]=$baseFolderCatalogo;
    $arrRet["pagination"] = $pagination;
    $arrRet["dir_and_file"] = $dir_and_files;

    return $arrRet;
  }

  /**
   * Visualizza la lista delle cartelle e delle foto presenti nella cartella
   *
   * @param string $absolute_path
   */
  private function showListFolder($absolute_path) {
    $map = $this->get_dirs_and_files_whit_pagination($absolute_path);
//    stampa_a_video_object($map);
    // if (is_guest_session())  {  //Negazione
    //   $map["dir_and_file"]["files"]=array();
    //   $map["pagination"]='';
    // }

    $map["querystring"]="?per_page=".(($this->pagination->cur_page-1)*$this->pagination->per_page);


    $this->load->view('vw_header');
    $this->load->view('section/' . __CLASS__ . '/vw_listfolder', $map);


    $this->load->view('vw_footer');
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

  /**
   * Gestione della richiesta specifica, tutte le richieste tranne il dettaglio verranno gestite qui
   * @param type $subfolder
   */
  public function gestioneMedia($subfolder = "") {
//    echo $subfolder ;
    $retval = $this->catalogofoto->type_content($subfolder);
    switch ($retval["code"]) {
      case "dir":
        $this->showListFolder($retval["source_media"]);
        break;
      case "file":
        // if (!is_guest_session()) {  //Negazione (dafault !)
          $this->catalogofoto->show_foto($retval["source_media"]);
        // } else {
        //   show_not_autorized();
        // }
        break;
      default:
        show_404(); //Risorsa non presente
        break;
    }
  }

  public function aggiungi_al_carrello() {
    /**
     * Struttura dati per il prodotto che va inserito nel carrello
     */
    $post_datas = array(
    "file_name" => ""
    , "file_path" => ""
    , "vars_file" => new type_prodotto_vars()
    , "vars_s" => new type_prodotto_vars()
    , "vars_m" => new type_prodotto_vars()
    , "vars_l" => new type_prodotto_vars()
    , "vars_xl" => new type_prodotto_vars()
    , "vars_xxl" => new type_prodotto_vars()
    , "vars_all" => new type_prodotto_vars()

    );

    $Prodotto = new type_prodotto();
    //Preparo la struttura dati del prodotto prelevandoli dal POST
    foreach ($post_datas as $K => $V) {
      if (gettype($V) != "string") {
        $Val = $this->input->post($K);
        foreach ($Val as $K1 => $V1) {
          $post_datas[$K]->$K1(($K1 == "qty" || $K1 == "iva" ? intval($V1) : doubleval($V1)));
        }
        $post_datas[$K]->set_totali_singolo_prodotto();
        $Prodotto->$K = $post_datas[$K];
      } else {
        $Prodotto->$K = $this->input->post($K);
      }
    }
    $Prodotto->inizialize();

    $cntInseriti = $this->carrellodellaspesa->inserisci_prodotti(array($Prodotto));

//    redirect('Carrello');
    redirect($this->input->post('redirecturi'));
  }

}
