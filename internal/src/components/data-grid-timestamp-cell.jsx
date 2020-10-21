import React, {useState, useCallback} from 'react'

const formatAMPM = (date) => {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours >= 12 ? 'PM' : 'AM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0'+minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	return strTime;
}


const DataGridTimestampCell = ({value: timestamp}) => {
	const date = new Date(timestamp*1000)
	const mmyydd = ((date.getMonth() > 8) ? (date.getMonth() + 1) : ('0' + (date.getMonth() + 1))) + '/' + ((date.getDate() > 9) ? date.getDate() : ('0' + date.getDate())) + '/' + (`${date.getFullYear()}`.substr(2))
	const hhmm = formatAMPM(date)
	return (
		<div className="date-time-cell">
			<div>{mmyydd}</div>
			<div>{hhmm}</div>
		</div>
	)
}

export default DataGridTimestampCell
