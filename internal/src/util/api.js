const fetchOptions = () => ({
	headers: {
		pragma: 'no-cache',
		'cache-control': 'no-cache'
	},
	method: 'GET',
	mode: 'cors',
	credentials: 'include'
})

// checks response for errors and decodes json
const handleErrors = async resp => {
	if (!resp.ok) throw Error(resp.statusText)
	const data = await resp.json()
	if (data?.errorID) {
		throw Error(data.message)
	}
	return data
}

const fetchGet = url => fetch(url, fetchOptions()).then(handleErrors)

export const apiVerifySession = () => fetchGet('/api/json.php/loRepository.getSessionValid')
export const apiGetCurrentUser = () =>
	fetchGet('/api/json.php/loRepository.getUser').then(user => {
		// normalize the data we're getting back
		const castToInt = ['createTime', 'lastLogin', 'userID']
		castToInt.forEach(key => {
			user[key] = parseInt(user[key], 10)
		})
		return user
	})
export const apiGetInstances = () =>
	fetchGet('/api/json.php/loRepository.getInstances').then(instances => {
		// normalize the data we're getting back
		const castToInt = [
			'createTime',
			'attemptCount',
			'allowScoreImport',
			'startTime',
			'endTime',
			'attemptCount',
			'userID',
			'instID'
		]
		instances.forEach(i => {
			castToInt.forEach(key => {
				i[key] = parseInt(i[key], 10)
			})
			i.allowScoreImport = i.allowScoreImport === '1'
			i.externalLink = i.externalLink !== null
		})
		return instances
	})
export const apiLogout = () => fetchGet('/api/json.php/loRepository.doLogout')
export const apiGetLOMeta = (r, loID) => fetchGet(`/api/json.php/loRepository.getLOMeta/${loID}`)
export const apiGetLO = (r, loID) =>
	fetchGet(`/api/json.php/loRepository.getLO/${loID}`).then(lo => {
		// normalize the data we're getting back
		const castToInt2 = ['qGroupID', 'userID']
		castToInt2.forEach(key => {
			lo.aGroup[key] = parseInt(lo.aGroup[key], 10)
		})
		castToInt2.forEach(key => {
			lo.pGroup[key] = parseInt(lo.pGroup[key], 10)
		})
		lo.aGroup.allowAlts = lo.aGroup.allowAlts === '1'
		lo.aGroup.rand = lo.aGroup.rand === '1'
		lo.pGroup.allowAlts = lo.pGroup.allowAlts === '1'
		lo.pGroup.rand = lo.pGroup.rand === '1'
		lo.createTime = parseInt(lo.createTime, 10)
		lo.allowScoreImport = lo.allowScoreImport === '1'
		lo.externalLink = lo.externalLink !== null
		lo.aGroup.kids.forEach(k => {
			k.questionID = parseInt(k.questionID, 10)
			if (k.items) {
				k.items.forEach(i => {
					if (i.media) {
						i.media.forEach(m => {
							m.mediaID = parseInt(m.mediaID, 10)
							m.auth = parseInt(m.auth, 10)
							m.createTime = parseInt(m.createTime, 10)
							m.height = parseInt(m.height, 10)
							m.width = parseInt(m.width, 10)
							m.length = parseInt(m.length, 10)
							m.size = parseInt(m.size, 10)
						})
					}
				})
			}
		})
		return lo
	})

export const getUsersMatchingSearch = (r, search) =>
	fetchGet(`/api/json.php/loRepository.getUsersMatchingSearch/${encodeURIComponent(search)}`).then(
		users => {
			users.forEach(u => {
				u.userID = parseInt(u.userID, 10)
			})
			return users
		}
	)

export const apiGetScoresForInstance = (r, instID) =>
	fetchGet(`/api/json.php/loRepository.getScoresForInstance/${instID}`).then(scoresByUser => {
		// normalize the data we're getting back
		const castToInt = ['attemptID', 'linkedAttempt', 'score', 'submitDate']
		scoresByUser.forEach(u => {
			u.additional = parseInt(u.additional, 10)
			u.userID = parseInt(u.userID, 10)
			u.attempts.forEach(a => {
				castToInt.forEach(key => {
					a[key] = parseInt(a[key], 10)
				})
			})
		})
		return scoresByUser
	})

export const apiEditExtraAttempts = ({ userID, instID, newCount }) => {
	return fetchGet(`/api/json.php/loRepository.editExtraAttempts/${userID}/${instID}/${newCount}`)
}
export const apiGetVisitTrackingData = (r, userID, instID) =>
	fetchGet(`/api/json.php/loRepository.getVisitTrackingData/${userID}/${instID}`).then(data => {
		data.visitLog.forEach(visit => {
			visit.logs.forEach(l => {
				const castToInt = ['trackingID', 'createTime', 'loID', 'visitID']
				castToInt.forEach(key => {
					l[key] = parseInt(l[key], 10)
				})

				const attempt = l?.attemptData?.attempt
				const scores = l?.attemptData?.scores
				if (attempt) {
					// castToInt
					const castToInt2 = [
						'attemptID',
						'endTime',
						'instID',
						'linkedAttemptID',
						'loID',
						'qGroupID',
						'score',
						'startTime',
						'userID',
						'visitID'
					]
					castToInt2.forEach(key => {
						attempt[key] = parseInt(attempt[key], 10)
					})
				}

				if (scores) {
					const castToInt3 = ['score', 'itemID']
					scores.forEach(s => {
						castToInt3.forEach(key => {
							s[key] = parseInt(s[key], 10)
						})
						// add the question index to each score
						s.orderIndex = attempt.qOrder.indexOf(s.itemID)
					})
				}
			})
		})
		return data
	})
export const apiGetInstanceTrackingData = (r, instID) =>
	fetchGet(`/api/json.php/loRepository.getInstanceTrackingData/${instID}`)
export const apiGetUserNames = (r, ...userIDs) =>
	fetchGet(`/api/json.php/loRepository.getUserNames/${userIDs.join(',')}`).then(users => {
		users.forEach(u => {
			u.userID = parseInt(u.userID, 10)
		})
		return users
	})
export const apiGetInstancePerms = (r, instID) =>
	fetchGet(`/api/json.php/loRepository.getItemPerms/${instID}/1`)
export const apiEditInstance = ({
	name,
	courseID,
	instID,
	startTime,
	endTime,
	attemptCount,
	scoreMethod,
	isImportAllowed
}) => {
	name = encodeURIComponent(name)
	instID = encodeURIComponent(instID)
	courseID = encodeURIComponent(courseID)
	startTime = encodeURIComponent(startTime)
	endTime = encodeURIComponent(endTime)
	attemptCount = encodeURIComponent(attemptCount)
	scoreMethod = encodeURIComponent(scoreMethod)
	isImportAllowed = isImportAllowed ? '1' : '0'

	return fetchGet(
		`/api/json.php/loRepository.editInstance/${name}/${instID}/${courseID}/${startTime}/${endTime}/${attemptCount}/${scoreMethod}/${isImportAllowed}`
	)
}
export const apiAddUsersToInstance = ({ instID, userIDs }) =>
	fetchGet(`/api/json.php/loRepository.addUsersToInstance/${instID}/${userIDs.join(',')}`)
export const apiRemoveUsersFromInstance = ({ instID, userIDs }) =>
	fetchGet(`/api/json.php/loRepository.removeUsersFromInstance/${instID}/${userIDs.join(',')}`)
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
export const apiDeleteInstance = ({ instID }) =>
	fetchGet(`/api/json.php/loRepository.removeInstance/${instID}`)
