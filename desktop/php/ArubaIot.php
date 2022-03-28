<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('ArubaIot');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());


?>
<script type="text/javascript">


  $(document).ready(function() {
    // do this stuff when the HTML is all ready
    refreshDeviceList();
 
 <?php   
  // ----- Depending on daemon include state, adapt display
  // TBC : I should not any more store the include status in jeedom attributes,  because available in daemon API
  $v_status = ArubaIot::getDaemonIncludeMode();
  //if (config::byKey('include_mode', 'ArubaIot', 0) == 1) {
  if ($v_status == 1) {
  	echo 'displayIncludeState(1);';
  } 
  else {
  	echo 'displayIncludeState(0);';
  }
?>
    

  });

</script>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
  <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>

      <div class="cursor changeIncludeState include card logoSecondary" data-state="0" onclick="swapIncludeState();">
        <i class="fas fa-sign-in-alt fa-rotate-90" ></i>
        <br>
        <span>{{Mode inclusion}}</span>
      </div>

      <div class="cursor eqLogicAttr displayReporters logoSecondary" data-l1key="toto" onclick="modal_reporters_display();">
        <i class="fas fa-sitemap"></i>
        <br>
        <span>{{Rapporteurs}}</span>
      </div>

      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
  </div>

        <div id="inclusion_message_container" class="alert alert-info" style="display:none;">
        <span> {{Nouveaux équipements détectés}} : </span><span id="inclusion_message_count" ></span>
        </div>


  <legend><i class="fas fa-table"></i> {{Mes Equipements}}</legend>
	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />


<?php
// Here I moved this part of the display to a modal file : modal.device_list.php
// By doing that I can refresh the list automatically, for
// exemple when in inclusion mode

?>
        <div id="device_list"></div>

</div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement ArubaIot}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement ArubaIot}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
foreach (jeeObject::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                   </select>
               </div>
           </div>
	   <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-9">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
               </div>
           </div>
	<div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		</div>
	</div>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Adresse MAC}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mac_address" placeholder="XX:XX:XX:XX:XX:XX"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Type d'objet}}
        <sup><i class="fa fa-question-circle tooltips" title="{{Pour laisser le plugin découvrir le type, choisir 'Découvrir automatiquement'.}}"></i></sup>
        </label>
        <div class="col-sm-3">
          <select id="cluster" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="class_type">
            <?php
              $v_list = ArubaIot::supportedDeviceType();
              foreach ($v_list as $v_index => $v_item) {
                echo '<option value="'.$v_index.'">'.$v_item.'</option>"';
              }
            ?>
          </select>
        </div>

    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Auto-découverte des commandes}}
        <sup><i class="fa fa-question-circle tooltips" title="{{Va ajouter les commandes au fur et à mesure de la réception des informations de télémétrie.}}"></i></sup>
        </label>
        <div class="col-sm-3">
          <input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="command_auto"/> {{Activer}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">&nbsp;</label>
        <div class="col-sm-3">

        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Nom du Fabriquant}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vendor_name"/ disabled>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Nom Local}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="local_name"/ disabled>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Modèle}}</label>
        <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="model"/ disabled>
        </div>
    </div>


</fieldset>
</form>
</div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a>
<br/><br/>

<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
</div>
</div>

</div>
</div>


<?php include_file('desktop', 'ArubaIot', 'js', 'ArubaIot');?>
<?php include_file('core', 'plugin.template', 'js');?>
