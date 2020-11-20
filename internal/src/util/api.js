const fetchOptions = () => ({
	headers: {
		pragma: 'no-cache',
		'cache-control': 'no-cache'
	},
	method: 'GET',
	mode: 'cors',
	credentials: 'include'
})

const handleErrors = async resp => {
	if (!resp.ok) throw Error(resp.statusText)
	const data = await resp.json()
	if (data.errorID) {
		throw Error(data.message)
	}
	return data
}

const fetchGet = url => fetch(url, fetchOptions()).then(handleErrors)

export const apiGetUser = () => fetchGet('/api/json.php/loRepository.getUser')
export const apiGetInstances = () => fetchGet('/api/json.php/loRepository.getInstances')
export const apiLogout = () => fetchGet('/api/json.php/loRepository.doLogout')
export const apiGetLOMeta = loID => fetchGet(`/api/json.php/loRepository.getLOMeta/${loID}`)
export const apiGetLO = loID => fetchGet(`/api/json.php/loRepository.getLO/${loID}`)
export const apiGetScoresForInstance = instID =>
	fetchGet(`/api/json.php/loRepository.getScoresForInstance/${instID}`)
export const apiEditExtraAttempts = (userID, instID, newCount) =>
	fetchGet(`/api/json.php/loRepository.editExtraAttempts/${userID}/${instID}/${newCount}`)
export const apiGetVisitTrackingData = (userID, instID) =>
	fetchGet(`/api/json.php/loRepository.getVisitTrackingData/${userID}/${instID}`)
export const apiGetInstanceTrackingData = instID =>
	fetchGet(`/api/json.php/loRepository.getInstanceTrackingData/${instID}`)
export const apiGetUserNames = userIDs =>
	fetchGet(`/api/json.php/loRepository.getUserNames/${userIDs.join(',')}`)
export const apiGetInstancePerms = instID =>
	fetchGet(`/api/json.php/loRepository.getItemPerms/${instID}/1`)
export const apiEditInstance = ({
	instanceName,
	courseName,
	instID,
	startTime,
	endTime,
	numAttempts,
	scoringMethod,
	isImportAllowed
}) =>
	fetchGet(
		`/api/json.php/loRepository.editInstance/${instanceName}/${instID}/${courseName}/${startTime}/${endTime}/${numAttempts}/${scoringMethod}/${
			isImportAllowed ? '1' : '0'
		}`
	)
export const apiGetResponsesForInstance = async (key, { instID }) => {
	if (!instID) return []

	const perPage = /* 10000 */ 1
	const addData = []
	let allLoaded = false
	let page = 0

	while (!allLoaded) {
		const offset = page * perPage
		const data = await fetchGet(
			`/api/json.php/loRepository.getResponsesForInstance/${instID}/${offset}/${perPage}`
		)
		page = page + 1
		addData.push(...data)
		if (data.length < perPage) allLoaded = true
	}

	return addData
}
