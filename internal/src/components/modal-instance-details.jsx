import React from 'react'
import PropTypes from 'prop-types'

export default function ModalInstanceDetails() {
	return <div>@TODO</div>
}

ModalInstanceDetails.defaultProps = {
	instanceName: '',
	courseName: '',
	startDate: null,
	endDate: null,
	numAttempts: 1,
	scoringMethod: 'highest',
	isImportAllowed: true
}

ModalInstanceDetails.propTypes = {
	onCancel: PropTypes.func.isRequired,
	onSave: PropTypes.func.isRequired,
	isExternallyLinked: PropTypes.bool.isRequired,
	mode: PropTypes.oneOf(['create', 'edit']).isRequired,
	instanceName: PropTypes.string,
	courseName: PropTypes.string,
	startDate: PropTypes.oneOfType([null, PropTypes.instanceOf(Date)]),
	endDate: PropTypes.oneOfType([null, PropTypes.instanceOf(Date)]),
	numAttempts: PropTypes.number,
	scoringMethod: PropTypes.oneOf(['highest', 'average', 'last']),
	isImportAllowed: PropTypes.bool
}
