<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-portit_import" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-cogs"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="portit-import panel-body">
        <!-- <p><?php echo $placeholder; ?></p> -->
        <input type="hidden" id="token" value="<?php echo $token; ?>">
        <div class="row">
          <div class="col-lg-4">
            <p>Загрузка в Market</p>
            <form action="/file-upload" class="portit-import__dropzone">
              <div class="fallback">
                <input name="file" type="file" multiple />
              </div>
              <div class="dz-message" data-dz-message><span>Нажмите или перетащите сюда файлы для загрузки</span></div>
            </form>
            <p>Содержимое папки Market</p>
            <ul class="portit-import__files-list" id="filesListMarket">
              <?php foreach ($marketFilesList as $file) { ?>
                <li>
                  <span><?php echo $file; ?></span><a href="#<?php echo $file; ?>" class="portit-import__files-item-remove" data-dir="market">Удалить</a>
                </li>
              <?php } ?>
            </ul>
          </div>
          <div class="col-lg-4">
            <p>Загрузка в Options</p>
            <form action="/file-upload" class="portit-import__dropzone">
              <div class="fallback">
                <input name="file" type="file" multiple />
              </div>
              <div class="dz-message" data-dz-message><span>Нажмите или перетащите сюда файлы для загрузки</span></div>
            </form>
          </div>
          <div class="col-lg-4">
            <p>Загрузка в Prices</p>
            <form action="/file-upload" class="portit-import__dropzone">
              <div class="fallback">
                <input name="file" type="file" multiple />
              </div>
              <div class="dz-message" data-dz-message><span>Нажмите или перетащите сюда файлы для загрузки</span></div>
            </form>
          </div>
        </div>
		    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-portit_import" class="form-horizontal">
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>