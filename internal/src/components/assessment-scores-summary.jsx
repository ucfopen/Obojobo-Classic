import React from 'react'
import PropTypes from 'prop-types'

export default function AssessmentScoresSummary() {
	return <div>@TODO</div>
}

AssessmentScoresSummary.propTypes = {
	scores: PropTypes.arrayOf(PropTypes.number).isRequired
}

// AssessmentScoresSummary.propTypes = {
// 	scoringMethod: PropTypes.oneOf(['highest', 'average', 'last']),
// 	scores: PropTypes.arrayOf(
// 		PropTypes.shape({
// 			userID: PropTypes.string.isRequired,
// 			user: PropTypes.shape({
// 				first: PropTypes.string,
// 				last: PropTypes.string,
// 				mi: PropTypes.string,
// 			}),
// 			additional: PropTypes.string,
// 			attempts: PropTypes.arrayOf(
// 				PropTypes.shape({
// 					attemptID: PropTypes.string.isRequired,
// 					score: PropTypes.string.isRequired,
// 					linkedAttempt: PropTypes.string.isRequired,
// 					submitted: PropTypes.bool.isRequired,
// 					submitDate: PropTypes.string.isRequired,
// 				})
// 			),
// 			synced: PropTypes.bool.isRequired,
// 			syncedScore: PropTypes.number.isRequired,
// 		})
// 	),
// }
