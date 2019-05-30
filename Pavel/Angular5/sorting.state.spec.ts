import SortingState from './sorting.state';

describe('SortingState', () => {

    it('should be created', () => {
        const state = new SortingState('timestamp', 'desc');

        expect(state).toBeTruthy();
        expect(state.getOrder()).toBe('desc');
        expect(state.getColumn()).toBe('timestamp');
    });

    it('change with the same field should work', () => {
        const state = new SortingState('timestamp', 'desc');

        state.change('timestamp');
        expect(state.getOrder()).toBe('asc');
        expect(state.getColumn()).toBe('timestamp');

        state.change('timestamp');
        expect(state.getOrder()).toBe('desc');
        expect(state.getColumn()).toBe('timestamp');
    });

    it('change with different field should work', () => {
        const state = new SortingState('timestamp', 'desc');

        state.change('subject');
        expect(state.getOrder()).toBe('desc');
        expect(state.getColumn()).toBe('subject');

        state.change('subject');
        expect(state.getOrder()).toBe('asc');
        expect(state.getColumn()).toBe('subject');
    });

});
