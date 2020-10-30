import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridStudentScores() {
	return <div>@TODO</div>
}

DataGridStudentScores.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			questionNumber: PropTypes.shape({
				displayNumber: PropTypes.number,
				altNumber: PropTypes.oneOfType([null, PropTypes.number])
			}),
			type: PropTypes.oneOf(['MC', 'QA', 'Media']),
			questionItems: PropTypes.arrayOf(
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
			),
			score: PropTypes.oneOfType([null, PropTypes.number])
		})
	),
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired
}
