function(doc) {
	if (doc.facebook) {
		if (doc.facebook.email) {
			emit(doc.facebook.email, 1);
		}
	}
	if (doc.github) {
		if (doc.github.email) {
			emit(doc.github.email, 1);
		}
	}
}
