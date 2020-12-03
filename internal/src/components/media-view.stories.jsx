import React from 'react'

import MediaView from './media-view'

export default {
	component: MediaView,
	title: 'MediaView',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <MediaView {...args} />

export const Pic = Template.bind({})
Pic.args = {
	media: {
		mediaID: 3341,
		auth: '6661',
		title: 'The Pride',
		itemType: 'pic',
		descText: 'Photo from Flickr 2',
		createTime: 1344543906,
		copyright:
			'Photo used under Creative Commons from <U><A HREF="event:http://www.flickr.com/photos/79723524@N03/7745511508">AbdillahAbi</A></U>',
		thumb: '0',
		url: 'The_Pride.jpg',
		size: '174927',
		length: '0',
		perms: null,
		height: 519,
		width: 640,
		meta: 0,
		attribution: 1,
		_explicitType: 'obo\\lo\\Media'
	}
}

export const Materia = Template.bind({})
Materia.args = {
	media: {
		mediaID: 6090,
		auth: '6661',
		title: 'Match the Law and Court Cases copy',
		itemType: 'kogneato',
		descText: '',
		createTime: 1533064081,
		copyright: 'Content from Materia.',
		thumb: '0',
		url: 'O2ZZA',
		size: '0',
		length: '0',
		perms: null,
		height: 0,
		width: 0,
		meta: {
			guest_access: false,
			$$hashKey: 'object:4',
			state: 'pending',
			created_at: '1533064020',
			name: 'Match the Law and Court Cases copy',
			id: 'O2ZZA',
			width: 0,
			is_embedded: false,
			clean_name: 'match-the-law-and-court-cases-copy',
			open_at: '-1',
			close_at: '-1',
			preview_url: 'https://materia.ucf.edu/preview/O2ZZA',
			student_access: false,
			embedded_only: false,
			widget: {
				package_hash: '0cae815d7d60ecb23a491f2479a9e316',
				in_catalog: '1',
				is_scalable: '0',
				question_types: '',
				dir: '14-matching/',
				is_answer_encrypted: '1',
				creator: 'creator.html',
				is_scorable: '1',
				meta_data: {
					supported_data: ['Question/Answer'],
					demo: 'NaOJs',
					about:
						'Matching provides a left and a right list. Students are asked to match the items on the left with the corresponding item on the right.',
					excerpt:
						'Students must match one set of words or phrases to a corresponding word, phrase, or definition.',
					features: ['Customizable', 'Scorable', 'Mobile Friendly', 'Media']
				},
				width: '750',
				is_playable: '1',
				clean_name: 'matching',
				group: 'Materia',
				api_version: '2',
				player: 'player.html',
				flash_version: '0',
				is_qset_encrypted: '1',
				name: 'Matching',
				id: '14',
				is_storage_enabled: '0',
				is_editable: '1',
				created_at: '1532011703',
				score_module: 'Matching',
				height: '548'
			},
			selected: true,
			attempts: '-1',
			is_draft: false,
			play_url: 'https://materia.ucf.edu/play/O2ZZA/match-the-law-and-court-cases-copy',
			height: 0,
			is_student_made: false,
			qset: { data: null, version: null },
			user_id: '110458',
			embed_url: 'https://materia.ucf.edu/embed/O2ZZA/match-the-law-and-court-cases-copy',
			img: 'https://static.materia.ucf.edu/widget/14-matching/img/icon-60.png',
			edit_url: 'https://materia.ucf.edu/my-widgets/#O2ZZA'
		},
		attribution: 0,
		_explicitType: 'obo\\lo\\Media'
	}
}

export const Flash = Template.bind({})
Flash.args = {
	media: {
		mediaID: 3520,
		auth: '6661',
		title: 'practice-pg-3-FL9',
		itemType: 'swf',
		descText: '',
		createTime: 1359066320,
		copyright: 'Copyright 2013 Zachary A. Berry.',
		thumb: '0',
		url: 'practice-pg-3-FL9.swf',
		size: '755793',
		length: '76',
		perms: null,
		height: 510,
		width: 780,
		meta: { version: 9, asVersion: 3 },
		attribution: 0,
		_explicitType: 'obo\\lo\\Media'
	}
}

export const Video = Template.bind({})
Video.args = {
	media: {
		mediaID: 4679,
		auth: '6661',
		title: 'CarCrash',
		itemType: 'flv',
		descText: '',
		createTime: 1466617300,
		copyright: 'Copyright 2016 Zachary A. Berry.',
		thumb: '0',
		url: 'CarCrash.flv',
		size: '1043772',
		length: '0',
		perms: null,
		height: 320,
		width: 436,
		meta: 0,
		attribution: 0,
		_explicitType: 'obo\\lo\\Media'
	}
}
