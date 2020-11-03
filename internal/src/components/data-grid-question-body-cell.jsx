import './data-grid-question-body-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'
import FlashHTML from './flash-html'

const renderMediaItem = ({ title, itemType }) => {
	switch (itemType) {
		case 'pic':
			return 'Image: ' + title

		case 'swf':
			return 'Flash: ' + title

		case 'flv':
			return 'Video: ' + title

		case 'mp3':
			return 'Audio: ' + title

		case 'kogneato':
			return 'Materia: ' + title
	}
}

const renderItem = ({ component, data, media }) => {
	switch (component) {
		case 'TextArea':
			return (
				<div className="text-area">
					<FlashHTML value={data} />
				</div>
			)

		case 'MediaView':
			return renderMediaItem(media[0])
	}
}

export default function DataGridQuestionBodyCell({ items }) {
	switch (items.length) {
		case 1:
			return (
				<div className="repository--data-grid-question-body-cell is-not-multiple-items">
					{renderItem(items[0])}
				</div>
			)

		case 2:
			return (
				<div className="repository--data-grid-question-body-cell is-multiple-items">
					<div className="row">
						<span className="label">L:</span>
						{renderItem(items[0])}
					</div>
					<div className="row">
						<span className="label">R:</span>
						{renderItem(items[1])}
					</div>
				</div>
			)
	}
}

DataGridQuestionBodyCell.propTypes = {
	items: PropTypes.arrayOf(
		PropTypes.shape({
			component: PropTypes.oneOf(['TextArea', 'MediaView']),
			data: PropTypes.string,
			media: PropTypes.arrayOf(
				PropTypes.shape({
					title: PropTypes.string,
					itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3'])
				})
			)
		})
	)
}
