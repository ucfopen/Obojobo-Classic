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

export const apiGetInstances = () => fetchGet('/api/json.php/loRepository.getInstances')

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
