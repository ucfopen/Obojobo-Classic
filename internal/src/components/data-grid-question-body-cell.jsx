import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridQuestionBodyCell() {
	return <div>@TODO</div>
}

DataGridQuestionBodyCell.propTypes = {
	items: PropTypes.arrayOf(
		PropTypes.shape({
			component: PropTypes.oneOf(['TextArea', 'MediaView']),
			data: PropTypes.string,
			media: PropTypes.arrayOf(
				PropTypes.shape({
					mediaID: PropTypes.number,
					title: PropTypes.string,
					itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3']),
					descText: PropTypes.string,
					width: PropTypes.number,
					height: PropTypes.number
				})
			)
		})
	)
}
