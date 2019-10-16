﻿/*
 * @file Video plugin for CKEditor
 * Copyright (C) 2011 Alfonso Martínez de Lizarrondo
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 */

( function() {

CKEDITOR.plugins.add( 'video',
{
	// Translations, available at the end of this file, without extra requests
	lang : [ 'en', 'es' ],

	getPlaceholderCss : function()
	{
		return 'img.cke_video' +
				'{' +
					'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/placeholder.png' ) + ');' +
					'background-position: center center;' +
					'background-repeat: no-repeat;' +
					'background-color:gray;'+
					'border: 1px solid #a9a9a9;' +
					'width: 80px;' +
					'height: 80px;' +
				'}';
	},

	onLoad : function()
	{
		// v4
		if (CKEDITOR.addCss)
			CKEDITOR.addCss( this.getPlaceholderCss() );

	},

	init : function( editor )
	{
		var lang = editor.lang.video;

		// Check for CKEditor 3.5
		if (typeof editor.element.data == 'undefined')
		{
			alert('The "video" plugin requires CKEditor 3.5 or newer');
			return;
		}

		CKEDITOR.dialog.add( 'video', this.path + 'dialogs/video.js' );

		editor.addCommand( 'Video', new CKEDITOR.dialogCommand( 'video' ) );
		editor.ui.addButton( 'Video',
			{
				label : lang.toolbar,
				command : 'Video',
				icon : this.path + 'images/icon.png'
			} );

		// v3
		if (editor.addCss)
			editor.addCss( this.getPlaceholderCss() );


		// If the "menu" plugin is loaded, register the menu items.
		if ( editor.addMenuItems )
		{
			editor.addMenuItems(
				{
					video :
					{
						label : lang.properties,
						command : 'Video',
						group : 'flash'
					}
				});
		}

		editor.on( 'doubleclick', function( evt )
			{
				var element = evt.data.element;

				if ( element.is( 'img' ) && element.data( 'cke-real-element-type' ) == 'video' )
					evt.data.dialog = 'video';
			});

		// If the "contextmenu" plugin is loaded, register the listeners.
		if ( editor.contextMenu )
		{
			editor.contextMenu.addListener( function( element, selection )
				{
					if ( element && element.is( 'img' ) && !element.isReadOnly()
							&& element.data( 'cke-real-element-type' ) == 'video' )
						return { video : CKEDITOR.TRISTATE_OFF };
				});
		}

		// Add special handling for these items
		CKEDITOR.dtd.$empty['cke:source']=1;
		CKEDITOR.dtd.$empty['source']=1;

		editor.lang.fakeobjects.video = lang.fakeObject;


	}, //Init

	afterInit: function( editor )
	{
		var dataProcessor = editor.dataProcessor,
			htmlFilter = dataProcessor && dataProcessor.htmlFilter,
			dataFilter = dataProcessor && dataProcessor.dataFilter;

		// dataFilter : conversion from html input to internal data
		dataFilter.addRules(
			{

			elements : {
				$ : function( realElement )
				{
						if ( realElement.name == 'video' )
						{
							realElement.name = 'cke:video';
							for( var i=0; i < realElement.children.length; i++)
							{
								if ( realElement.children[ i ].name == 'source' )
									realElement.children[ i ].name = 'cke:source'
							}

							var fakeElement = editor.createFakeParserElement( realElement, 'cke_video', 'video', false ),
								fakeStyle = fakeElement.attributes.style || '';

							var width = realElement.attributes.width,
								height = realElement.attributes.height,
								poster = realElement.attributes.poster,
								responsive = realElement.attributes.responsive;

							if ( typeof width != 'undefined' )
								fakeStyle = fakeElement.attributes.style = fakeStyle + 'width:' + CKEDITOR.tools.cssLength( width ) + ';';

							if ( typeof height != 'undefined' )
								fakeStyle = fakeElement.attributes.style = fakeStyle + 'height:' + CKEDITOR.tools.cssLength( height ) + ';';

							if ( poster )
								fakeStyle = fakeElement.attributes.style = fakeStyle + 'background-image:url(' + poster + ');';

							if (typeof responsive != 'undefined' && responsive && responsive !== 'null') {
								fakeElement.addClass('embed-responsive-item');
							}

							return fakeElement;
						}
				}
			}

			}
		);

	} // afterInit

} ); // plugins.add


var en = {
  toolbar: 'Video',
  dialogTitle: 'Video properties',
  fakeObject: 'Video',
  properties: 'Edit video',
  widthRequired: 'Width field cannot be empty',
  heightRequired: 'Height field cannot be empty',
  poster: 'Poster image',
  sourceVideo: 'Source video',
  sourceType: 'Video type',
  linkTemplate: '<a href="%src%">%type%</a> ',
  fallbackTemplate: 'Your browser doesn\'t support video.<br>Please download the file: %links%',
  infoLabel: 'Information',
  html360: 'This feature (only MP4 videos) is currently still in BETA mode.<br />It only works on dynamic pages, not inside documents created<br />in the documents tool or seen through learning paths.<br />Please do not add more than one 360° video on a single page<br /> as more than one on the same page might generate conflicts.',
  video360: 'Enable 360° video player',
  video360stereo: 'Stereo video (1:1 aspect ratio)',
  responsive: 'Resposive size (mobile-optimized)',
  ratio16by9: '16:9 aspect ratio',
  ratio4by3: '4:3 aspect ratio'
};

var es = {
  toolbar: 'Vídeo',
  dialogTitle: 'Propiedades del vídeo',
  fakeObject: 'Vídeo',
  properties: 'Editar el vídeo',
  widthRequired: 'La anchura no se puede dejar en blanco',
  heightRequired: 'La altura no se puede dejar en blanco',
  poster: 'Imagen de presentación',
  sourceVideo: 'Archivo de vídeo',
  sourceType: 'Tipo',
  linkTemplate: '<a href="%src%">%type%</a> ',
  fallbackTemplate: 'Su navegador no soporta el tag video.<br>Por favor, descargue el fichero: %links%',
  infoLabel: 'Información',
  html360: 'Esta funcionalidad (sólo MP4) todavía se encuentra en modo BETA.<br />Sólo funciona en páginas dinámicas, mas no dentro de documentos<br />creados en la herramienta de documentos o visualizados a través<br />de las lecciones.<br />Por favor no colocar más de un vídeo 360° en una misma página<br />ya que puede provocar conflictos y bloquearlos todos.',
  video360: 'Habilitar reproductor de vídeo 360°',
  video360stereo: 'Vídeo estéreo (relación de aspecto 1:1)',
  responsive: 'Tamaño adaptable (tamaño optimizado para móviles)',
  ratio16by9: 'Relación de aspecto 16:9',
  ratio4by3: 'Relación de aspecto 4:3'
};

var fr = {
	toolbar: 'Vidéo',
	dialogTitle: 'Propiétés de la vidéo',
	fakeObject: 'Vidéo',
	properties: 'Éditer la vidéo',
	widthRequired: 'La largeur ne peut pas être vide',
	heightRequired: 'La hauteur ne peut pas être vide',
	poster: 'Image de prévisualisation',
	sourceVideo: 'Fichier vidéo',
	sourceType: 'Type',
	linkTemplate: '<a href="%src%">%type%</a> ',
	fallbackTemplate: 'Votre navigateur ne supporte pas le tag video.<br>Merci de télécharger la vidéo ici: %links%',
	infoLabel: 'Information',
	html360: 'Cette fonctionnalité (MP4 uniquement) est actuellement en mode BETA.<br />Elle ne fonctionne que sur les pages dynamiques, et pas<br />dans les documents créés à partir de l\'outil document ou visualisés<br />au travers de l\'outil parcours.<br />Merci de ne pas placer plus d\'une vidéo 360° par page. Cela<br />peut causer des conflits et toutes les rendre inactives.',
	video360: 'Activer la visualisation 360°',
	video360stereo: 'Vidéo stéréo (proportions 1:1 / apparence de 2 vidéos superposées)',
	responsive: 'Resposive',
	ratio16by9: '16:9 aspect ratio',
	ratio4by3: '4:3 aspect ratio'
};

// v3
if (CKEDITOR.skins)
{
	en = { video : en} ;
	es = { video : es} ;
	fr = { video : fr} ;
}

// Translations
CKEDITOR.plugins.setLang( 'video', 'en', en );
CKEDITOR.plugins.setLang( 'video', 'es', es );
CKEDITOR.plugins.setLang( 'video', 'fr', fr );

})();