import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridScoresByQuestion() {
	return <div>@TODO</div>
}

DataGridScoresByQuestion.propTypes = {
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
			mean: PropTypes.oneOfType([null, PropTypes.number])
		})
	)
}

// DataGridScoresByQuestion.propTypes = {
// 	responses: PropTypes.arrayOf(
// 		PropTypes.shape({
// 			createTime: PropTypes.string,
// 			userID: PropTypes.string,
// 			itemID: PropTypes.string,
// 			answer: PropTypes.string,
// 			score: PropTypes.string,
// 		})
// 	),
// 	questions: PropTypes.arrayOf(
// 		PropTypes.shape({
// 			questionID: PropTypes.string,
// 			itemType: PropTypes.oneOf(['MC', 'QA', 'Media']),
// 			answers: PropTypes.arrayOf(
// 				PropTypes.shape({
// 					answerID: PropTypes.string,
// 					answer: PropTypes.string,
// 					weight: PropTypes.number,
// 				})
// 			),
// 			items: PropTypes.arrayOf(
// 				PropTypes.shape({
// 					component: PropTypes.oneOf(['TextArea', 'MediaView']),
// 					data: PropTypes.string,
// 					media: PropTypes.arrayOf(
// 						PropTypes.shape({
// 							mediaID: PropTypes.number,
// 							title: PropTypes.string,
// 							itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3']),
// 							descText: PropTypes.string,
// 							width: PropTypes.number,
// 							height: PropTypes.number,
// 						})
// 					),
// 				})
// 			),
// 		})
// 	),
// 	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
// 	onSelect: PropTypes.func.isRequired,
// }
