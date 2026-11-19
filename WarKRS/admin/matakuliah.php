<?php
require __DIR__.'/_crud.php';
render_crud('Mata Kuliah','mata_kuliah','matakuliah',[
'kode'=>['label'=>'Kode Mata Kuliah','required'=>true],
'nama'=>['label'=>'Nama Mata Kuliah','required'=>true],
'sks'=>['label'=>'Jumlah SKS','type'=>'number','required'=>true],
'semester'=>['label'=>'Semester','type'=>'number','required'=>true]
],['Kode'=>'kode','Nama Mata Kuliah'=>'nama','SKS'=>'sks','Semester'=>'semester']);
